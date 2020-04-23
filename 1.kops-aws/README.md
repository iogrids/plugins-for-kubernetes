# Installing Kubernetes on AWS using KOPS

## 1. Install KOPS CLI

```
curl -LO https://github.com/kubernetes/kops/releases/download/$(curl -s https://api.github.com/repos/kubernetes/kops/releases/latest | grep tag_name | cut -d '"' -f 4)/kops-linux-amd64
chmod +x kops-linux-amd64
sudo mv kops-linux-amd64 /usr/local/bin/kops
```

## 2. Install kubectl

```
curl -LO https://storage.googleapis.com/kubernetes-release/release/`curl -s https://storage.googleapis.com/kubernetes-release/release/stable.txt`/bin/linux/amd64/kubectl
chmod +x ./kubectl
sudo mv ./kubectl /usr/local/bin/kubectl
```

## 3. Install IAM user and attach policy

```
aws iam create-group --group-name kops

aws iam attach-group-policy --policy-arn arn:aws:iam::aws:policy/AmazonEC2FullAccess --group-name kops
aws iam attach-group-policy --policy-arn arn:aws:iam::aws:policy/AmazonRoute53FullAccess --group-name kops
aws iam attach-group-policy --policy-arn arn:aws:iam::aws:policy/AmazonS3FullAccess --group-name kops
aws iam attach-group-policy --policy-arn arn:aws:iam::aws:policy/IAMFullAccess --group-name kops
aws iam attach-group-policy --policy-arn arn:aws:iam::aws:policy/AmazonVPCFullAccess --group-name kops

aws iam create-user --user-name kops

aws iam add-user-to-group --user-name kops --group-name kops

aws iam create-access-key --user-name kops
```

You should record the SecretAccessKey and AccessKeyID in the returned JSON output, and then use them below:

## 4. Setup AWS CLI

```
# configure the aws client to use your new IAM user
aws configure           # Use your new access and secret key here
aws iam list-users      # you should see a list of all your IAM users here

# Because "aws configure" doesn't export these vars for kops to use, we export them now
export AWS_ACCESS_KEY_ID=$(aws configure get aws_access_key_id)
export AWS_SECRET_ACCESS_KEY=$(aws configure get aws_secret_access_key)
```

## 5. Create s3 bucket

```
aws s3api create-bucket \
    --bucket PROVIDE-UNIQUE-BUCKET-NAME-HERE \
    --region us-east-1
```

## 6. Cluster configuration

```
export NAME=PROVIDE-CLUSTER-NAME-HERE.k8s.local
export KOPS_STATE_STORE=s3://prefix-example-com-state-store   # Provide bucket name
```

## 7. Setup Cluster

Note: us-west-2a us-west-2b are availability zones. Availability zones are seperate buildings where your server is located in a given region which us us-west-2. 
To identify the list of availibility zones in us-west-2 region who can use the command below

```
aws ec2 describe-availability-zones --region us-west-2

```

In the below command you can mention all the availability zones where your server should be created as us-west-2a,us-west-2b

```
kops create cluster \
    --zones us-west-2a,us-west-2b \
    ${NAME}

```

## 8. create SSH key

```
ssh-keygen -b 2048 -t rsa -f ~/.ssh/id_rsa   #Hit enter key for all options
kops create secret --name ${NAME} sshpublickey admin -i ~/.ssh/id_rsa.pub

```

## Edit cluster (OPTIONAL - Use this if you want to edit the configuration of master or slave nodes)

```
kops edit cluster ${NAME}

# To edit the nodes 
kops edit ig nodes --name ${NAME}   # If you want to edit the nodes created in the cluster. You can increase the maxsize and minsize of nodes required

# To edit the master node
kops get ig --name ${NAME}  # This command will provide the name of the master
kops edit ig REPLACE-WITH-NAME-OF-THE-MASTER --name ${NAME}

```

## 9. Create the cluster

```
kops update cluster ${NAME} --yes

```

---

# Deleting the kubernetes cluster

!NOTE: WAIT TILL THE CLUSTER GETS DELETED, Check loadbalancer section, volume section, ec2 instance section, auto scaling groups and confirm whether all resources are deleted.

```
export NAME=PROVIDE-CLUSTER-NAME-HERE-WHICH-WAS-USED-WHEN-CREATING-CLUSTER.k8s.local
kops delete cluster --name ${NAME} --yes 
```




