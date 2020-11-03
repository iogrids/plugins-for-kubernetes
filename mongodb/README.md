mongodb://<username>:<password>@mongo-0.mongo.mongo-namespace.svc.cluster.local,mongo-1.mongo.mongo-namespace.svc.cluster.local,mongo-2.mongo.mongo-namespace.svc.cluster.local:27017/<mydb>?replicaSet=<myRsName>?authSource=admin

Example:

mongodb://jeriljose:M!cr0507t@mongo-0.mongo.default.svc.cluster.local,mongo-1.mongo.default.svc.cluster.local,mongo-2.mongo.default.svc.cluster.local:27017/<mydb>?replicaSet=<myRsName>?authSource=admin

Reference:

https://switchit-conseil.com/2019/10/16/deploy-a-secured-high-availability-mongodb-replica-set-on-kubernetes/