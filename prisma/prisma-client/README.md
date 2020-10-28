## 1. copy generated folder from 2.prisma-schema to src/

## 2. Make the below mentioned changes 

    Now go to /src/generated/prisma-client/index.js and change the below line

    ```
    endpoint: `http://localhost:4466/tracker/prod`,
    ```

    TO 

    ```
    endpoint: `http://prisma-secondary-service:4466/tracker/prod`,
    ```

    Note: prisma-secondary-service:4466 is the name of the prisma-secondary service server. prisma-nexus connects to 
          the secondary server for querying from the database. The prisma-secondary service can be scaled to any number of pods.
            

## 2. Install docker (To build image of prisma nexus)

```
# INSTALL DOCKER (REQUIRED FOR SKAFFOLD)
     curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
     sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable"
     sudo apt-get update
     sudo apt-get install -y docker-ce 
     sudo usermod -aG docker ${USER} (Login and logout if you are not able to login to gitlab registry: su -s ${USER} )

```
 
## 3. Build prisma express and push to gitlab registry

```
1. docker login registry.gitlab.com
2. docker build -t registry.gitlab.com/analytics-tracker/2.prisma .
3. docker push registry.gitlab.com/analytics-tracker/2.prisma

```
