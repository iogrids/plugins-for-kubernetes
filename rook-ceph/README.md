# Install rook-operator

1. kubectl create -f rook-operator.yaml

## Wait till all pods are running 

```
kubectl get pods -n rook-system
```

2. kubectl apply -f rook-cluster.yaml

## Wait till all pods are running

```
kubectl get pods -n rook
```

3. kubectl apply -f rook-storageclass-fs.yaml

## Wait till all pods are running
 
 ```
  kubectl get pods -n rook
 ```

4. kubectl apply -f rook-tools.yaml

rook-tools is a pod which works like a monitoring system for rook. You can bash into this pod and use a CLI called rookctl to check the status of the rook-ceph as shown below

```
kubectl exec -it rook-tools -n rook -- bash 
rookctl status # To check the status of rook-ceph. rookctl is the CLI which is pre-installed in this pod. Check for the below line                 OVERALL STATUS: OK 
```

Note: To use rook in mongodb or other pods

```
storageClassName: rook-block 
```
