#!/bin/bash
set -e

if [ -z "${GITHUB_TOKEN}" ]; then
	echo "please configure a github token to perform deploy"
	exit 1
fi

git config --global url."https://${GITHUB_TOKEN}@github.com/".insteadOf git@github.com:

# ==> Copy ansible scripts into container
git clone git@github.com:ushahidi/rollcall-infra.git /playbooks --depth=5

if [ -n "${ANSIBLE_VAULT_PASSWORD}" ]; then
  /bin/echo -e "${ANSIBLE_VAULT_PASSWORD}" > /playbooks/vpass
fi

# Append to ansible.cfg
envsubst >> /opt/ansible.cfg << EOM

[ssh_connection]
control_path=/dev/shm/ansible-ssh-${CI_BUILD_ID}-%%h-%%p-%%r
EOM

cd /playbooks

# ==> Get latest deployment code from github
ansible-galaxy install -r roles.yml

# ==> Obtain the latest state from terraform S3 bucket
pushd tf
terraform remote config \
    -backend=s3 \
    -backend-config="bucket=ushahidi-terraform-states" \
    -backend-config="key=rollcall/staging/terraform.tfstate" \
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
