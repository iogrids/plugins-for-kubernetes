1. cd /tmp
2. curl https://raw.githubusercontent.com/kubernetes/helm/master/scripts/get > install-helm.sh
3. chmod u+x install-helm.sh
4. ./install-helm.sh
5. helm init
6. kubectl -n kube-system create serviceaccount tiller
7. kubectl create clusterrolebinding tiller --clusterrole cluster-admin --serviceaccount=kube-system:tiller
8. helm init --service-account tiller

To verify that Tiller is running, list the pods in thekube-system namespace

1. kubectl get pods --namespace kube-system

The Tiller pod name begins with the prefix tiller-deploy-.

   
