helm install stable/rabbitmq --name rabbitmq -f values.yaml

## To update helm values

helm upgrade -f values.yaml rabbitmq stable/rabbitmq
