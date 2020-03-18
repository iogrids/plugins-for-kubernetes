1. chmod +x install.sh
2. ./install.sh

To verify that Tiller is running, list the pods in thekube-system namespace

1. kubectl get pods --namespace kube-system

The Tiller pod name begins with the prefix tiller-deploy-.

   
