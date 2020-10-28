# 1. To pull the docker image from private registry

```
kubectl create secret docker-registry gitlab-registry --docker-email="jerilcj1@gmail.com" --docker-username="jerilcj1" --docker-server="https://registry.gitlab.com/" --docker-password="aljecljo"
```

# 2. For CORS i.e to make REACT APP to access prisma-nexus provide the below domain setting in configmap.yaml 

```
    REACT_APP_URL: https://hatkit.xyz
```

# Deploy k8s files

```
kubectl apply -f .
```

