Initiate and configure the MongoDB replica set

kubectl exec -it mongo-0 bash

> mongo

> rs.initiate()

> var cfg = rs.conf();cfg.members[0].host="mongo-0.mongo:27017";rs.reconfig(cfg)

> rs.add("mongo-1.mongo:27017")
> rs.add("mongo-2.mongo:27017")


# Reference: https://developer.ibm.com/technologies/containers/tutorials/cl-deploy-mongodb-replica-set-using-ibm-cloud-container-service/