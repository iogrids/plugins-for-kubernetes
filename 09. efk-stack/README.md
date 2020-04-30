# EFK Stack

1. Apply storage class to store the logs

```
kubectl apply -f storage-aws.yaml  # For DigitalOcean this file is not required. This is only for AWS. In elastic-stack provide 
                                     [storageClassName: cloud-ssd] for AWS   
```

2. Apply config files and the elastic stack

```
kubectl apply -f fluentd-config.yaml
kubectl apply -f elastic-stack.yaml
 
```

3. All pods will be in kube-system namespace

```
kubectl get pods -n kube-system

```
