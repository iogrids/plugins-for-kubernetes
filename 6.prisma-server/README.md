1. Provide rabbitmq username and password 
2. kubectl apply -f .

To expose the prisma server 

    kubectl port-forward primary-deployment-<PROVIDE POD NAME> 4466:4466


Note:

    Whenever you make any schema changes expose the primary prisma server pod and execute the prisma deploy command
    primary prisma server will only run a single instance and prisma secondary can run multiple instances
