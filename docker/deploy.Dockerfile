FROM ubuntu:trusty

ENV ANSIBLE_VERSION 2.1.2.0
ENV TERRAFORM_VERSION 0.8.7

RUN apt-get update && \
    apt-get install -y python-dev python-pip git libffi6 libffi-dev libssl1.0.0 libssl-dev unzip wget gettext && \
    pip install ansible==${ANSIBLE_VERSION} && \
    apt-get remove -y python-dev libffi-dev libssl-dev && \
    apt-get autoremove -y && \
    apt-get clean && \
    wget -P /tmp https://releases.hashicorp.com/terraform/${TERRAFORM_VERSION}/terraform_${TERRAFORM_VERSION}_linux_amd64.zip && \
    unzip /tmp/terraform_${TERRAFORM_VERSION}_linux_amd64.zip -d /usr/bin && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY docker/deploy.run.sh /deploy.run.sh

ENV ANSIBLE_HOST_KEY_CHECKING False

ENTRYPOINT [ "/bin/bash", "/deploy.run.sh" ]
CMD [ "/bin/bash" ]
