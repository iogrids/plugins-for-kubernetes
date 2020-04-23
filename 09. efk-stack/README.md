# EFK Stack

```
kubectl apply -f storage-aws.yaml
kubectl apply -f fluentd-config.yaml
kubectl apply -f elastic-stack.yaml
 
```

All pods will be in kube-system namespace

```
kubectl get pods -n kube-system

```
