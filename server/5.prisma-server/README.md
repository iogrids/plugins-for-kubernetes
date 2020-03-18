kubectl apply -f .

To expose the prisma server 

    kubectl port-forward primary-deployment-<PROVIDE POD NAME> 4466:4466
