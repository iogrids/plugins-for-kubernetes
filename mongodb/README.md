# Connection String

```
mongodb://<username>:<password>@mongo-0.mongo.mongo-namespace.svc.cluster.local,mongo-1.mongo.mongo-namespace.svc.cluster.local,mongo-2.mongo.mongo-namespace.svc.cluster.local:27017/<mydb>?replicaSet=<myRsName>?authSource=admin

Example:

mongodb://jeriljose:M!cr0507t@mongo-0.mongo.default.svc.cluster.local,mongo-1.mongo.default.svc.cluster.local,mongo-2.mongo.default.svc.cluster.local:27017/<mydb>?replicaSet=rs0?authSource=admin
```

# Configuring mongodb replica set

```
> kubectl exec -it mongo-0 bash

> mongo

> rs.initiate()

> var cfg = rs.conf();cfg.members[0].host="mongo-0.mongo:27017";rs.reconfig(cfg)

> rs.add("mongo-1.mongo:27017")
> rs.add("mongo-2.mongo:27017")
> rs.status()
```

Reference:

https://switchit-conseil.com/2019/10/16/deploy-a-secured-high-availability-mongodb-replica-set-on-kubernetes/