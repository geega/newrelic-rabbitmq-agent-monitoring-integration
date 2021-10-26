#!/bin/bash
set -e
# Place file with newrelic license key
cat > /etc/newrelic-infra.yml <<EOF
---
# New Relic config file
license_key: ${NR_LICENSE_KEY}
EOF
# Place file with rabbitmq config
cat > /etc/newrelic-infra/integrations.d/rabbitmq-config.yml <<EOF
integrations:
  - name: nri-rabbitmq
    command: all
    env:
      HOSTNAME: ${RABBITMQ_ENDPOINT}
      PORT: ${RABBITMQ_PORT}
      CA_BUNDLE_DIR: /etc/ssl/certs
      USERNAME: ${RABBITMQ_USERNAME}
      PASSWORD: ${RABBITMQ_PASSWORD}
      USE_SSL: ${RABBITMQ_USE_SSL}
      NODE_NAME_OVERRIDE: local_node_name0002
      QUEUES_REGEXES: '${RABBITMQ_QUEUES_REGEXES}'
      EXCHANGES_REGEXES: '${RABBITMQ_EXCHANGE_REGEXES}'

EOF
exec "$@"
