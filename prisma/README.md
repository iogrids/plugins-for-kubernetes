## 1. Install nodejs (To use prisma deploy)

```
1. sudo apt-get update && sudo apt-get -y upgrade
2. curl -sL https://deb.nodesource.com/setup_10.x | sudo -E bash -
3. sudo apt install nodejs -y

```

## 2. PRISMA CLI (To use prisma deploy)

```
sudo npm install -g prisma
```

## 3. Start the prisma server  
```
1. kubectl port-forward primary-deployment-<PROVIDE POD NAME> 4466:4466 &
```

## 4. Deploy database schema to prisma server

```
1. prisma deploy (Provide end endpoint in prisma.yml. This command creates the schema in the database)
```

## 5. Copy generated folder to 3.prisma-nexus/src/
