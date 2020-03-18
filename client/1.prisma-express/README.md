# To pull the docker image from private registery

kubectl create secret docker-registry gitlab-registry --docker-email="EMAIL" --docker-username="USERNAME" --docker-server="https://registry.gitlab.com/" --docker-password="PASSWORD"

