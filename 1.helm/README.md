# Steps to install helm version 2

```
wget https://storage.googleapis.com/kubernetes-helm/helm-v2.9.1-linux-amd64.tar.gz
tar zxvf helm-v2.9.1-linux-amd64.tar.gz
mv linux-amd64/helm /usr/local/bin/
rm helm-v2.9.1-linux-amd64.tar.gz
rm -rf ./linux-amd64/
helm init
```

```
kubectl create serviceaccount --namespace kube-system tiller
kubectl create clusterrolebinding tiller-cluster-rule --clusterrole=cluster-admin --serviceaccount=kube-system:tiller
kubectl patch deploy --namespace kube-system tiller-deploy -p '{"spec":{"template":{"spec":{"serviceAccount":"tiller"}}}}' 
```