1. chmod +x install.sh
2. ./install.sh

To verify that Tiller is running, list the pods in thekube-system namespace

1. kubectl get pods --namespace kube-system

The Tiller pod name begins with the prefix tiller-deploy-.


# To list the packages installed by helm

```
helm ls
```

# To install a package using helm

```
syntax:  helm install --name  --namespace  
example: helm install --name cert-manager --namespace kube-system stable/cert-manager
```

# To delete a package installed by helm

```
syntax:  helm del --purge 
example: helm del --purge my-ingress # to delete my-ingress 
```
