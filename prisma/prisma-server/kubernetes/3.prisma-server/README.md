1. kubectl apply -f .

2. Now port-forward prisma server so prisma nexus can be deployed. To port-forward use the command below

```
kubectl port-forward primary-deployment-<PROVIDE POD NAME> 4466:4466 &

```


