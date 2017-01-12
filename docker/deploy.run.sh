#!/bin/bash
set -e

ROLLCALL_INFRA_BRANCH=${ROLLCALL_INFRA_BRANCH:-master}

# Parse environment assignations from arguments
while true; do
	if [[ "$1" =~ ^[^=]+=.*$ ]]; then
		echo "+ ${1%%=*} <- ${1#*=}"
		export ${1%%=*}=${1#*=}
		shift
		continue
	fi
	break
done

if [ -z "${GITHUB_TOKEN}" ]; then
	echo "please configure a github token to perform deploy"
	exit 1
fi

git config --global url."https://${GITHUB_TOKEN}@github.com/".insteadOf git@github.com:

# ==> Copy ansible scripts into container
[ ! -d $HOME/.ssh ] || true && mkdir -m 0700 $HOME/.ssh
ssh-keyscan github.com >> $HOME/.ssh/known_hosts
git clone -b ${ROLLCALL_INFRA_BRANCH} git@github.com:ushahidi/rollcall-infra.git /playbooks --depth=5

if [ -n "${ANSIBLE_VAULT_PASSWORD}" ]; then
	printf "%s" "${ANSIBLE_VAULT_PASSWORD}" > /playbooks/vpass
fi

# Set up key
if [ -n "${ANSIBLE_ROLLCALL_SSH_KEY}" ]; then
	echo -e "${ANSIBLE_ROLLCALL_SSH_KEY}" > /playbooks/id_ansible
	chmod 600 /playbooks/id_ansible
fi

# Append to ansible.cfg
envsubst >> /playbooks/ansible.cfg << EOM

private_key_file=/playbooks/id_ansible

[ssh_connection]
control_path=/dev/shm/ansible-ssh-${CI_BUILD_ID}-%%h-%%p-%%r
EOM

cd /playbooks

# ==> Get latest deployment code from github
ansible-galaxy install -r roles.yml

# ==> Obtain the latest state from terraform S3 bucket
pushd tf/${ENV}
terraform remote config \
    -backend=s3 \
    -backend-config="bucket=ushahidi-terraform-states" \
    -backend-config="key=rollcall/${ENV}/terraform.tfstate" \
    -backend-config="region=us-east-1"
terraform get
terraform remote pull
popd

# Perform variable substitution on parameters
#   i.e. if we get a parameter myvar="$CI_BRANCH" we substitute $CI_BRANCH for
#        its actual value in the environment, and get i.e. myvar="master"
args=()
for p in $@; do
    args+=(`printf '%s\n' $p | envsubst`)
done

# Execute parameter passed in arguments
echo executing: "${args[@]}"

exec "${args[@]}"
