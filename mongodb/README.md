# Connection String

```
mongodb://<username>:<password>@mongo-0.mongo.mongo-namespace.svc.cluster.local,mongo-1.mongo.mongo-namespace.svc.cluster.local,mongo-2.mongo.mongo-namespace.svc.cluster.local:27017/<mydb>?replicaSet=<myRsName>?authSource=admin

Example:

mongodb://mongo-0.mongo.default.svc.cluster.local,mongo-1.mongo.default.svc.cluster.local,mongo-2.mongo.default.svc.cluster.local:27017/<mydb>?replicaSet=rs0?authSource=admin
```

# Configuring mongodb replica set

```
> kubectl exec -it mongo-0 bash

> mongo

rs.initiate({_id: "rs0", version: 1, members: [
       { _id: 0, host : "mongo-0.mongo.default.svc.cluster.local:27017" },
       { _id: 1, host : "mongo-1.mongo.default.svc.cluster.local:27017" },
       { _id: 2, host : "mongo-2.mongo.default.svc.cluster.local:27017" }
 ]});

Reference:

https://switchit-conseil.com/2019/10/16/deploy-a-secured-high-availability-mongodb-replica-set-on-kubernetes/