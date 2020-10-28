# STEPS

1. kubectl apply -f mandatory.yaml
2. kubectl apply -f cloud-generic.yaml 
3. Set DNS in namecheap.com after getting the LoadBalancer IP address 

# To check for LoadBalancer IP address
 
 1. kubectl get svc --namespace=ingress-nginx


# To confirm Ingress Controller pods have started

 1. kubectl get pods --all-namespaces -l app.kubernetes.io/name=ingress-nginx

