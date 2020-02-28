helm repo add stable https://kubernetes-charts.storage.googleapis.com/
helm install --name mongodb -f values.yaml stable/mongodb-replicaset
  

# Connecting to mongodb
