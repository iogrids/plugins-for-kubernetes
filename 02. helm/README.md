# Steps to install helm version 2

```
cd /tmp
curl https://raw.githubusercontent.com/kubernetes/helm/master/scripts/get > install-helm.sh
chmod u+x install-helm.sh
./install-helm.sh
helm init
kubectl -n kube-system create serviceaccount tiller
kubectl create clusterrolebinding tiller --clusterrole cluster-admin --serviceaccount=kube-system:tiller
helm init --service-account tiller
```

To verify whether tiller is running

```
kubectl get pods --namespace kube-system
```

You can also check 

```
helm version
```


Note: If you are unable to install helm packages in the default namespace run the following commands

```
kubectl create serviceaccount --namespace kube-system tiller
kubectl create clusterrolebinding tiller-cluster-rule --clusterrole=cluster-admin --serviceaccount=kube-system:tiller
kubectl patch deploy --namespace kube-system tiller-deploy -p '{"spec":{"template":{"spec":{"serviceAccount":"tiller"}}}}'
```