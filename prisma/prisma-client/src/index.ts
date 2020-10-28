require('dotenv').config()
const prisma = require('./generated/prisma-client')
const { createServer } = require('http')
const { PubSub } = require('graphql-subscriptions')
const  schema  = require('./schema.ts')
const { runHttpQuery } = require("apollo-server-core");
const { ApolloServer, gql } = require('apollo-server-express');
var cors = require('cors')
const jwt = require('jsonwebtoken');
const bodyParser = require('body-parser');
var express = require('express');
const fileUpload = require('express-fileupload');

//provide the IP:PORT or URL or react application. React is a seperate microservice and prisma express is a seperate microservice and these apps need to communicate with each other

const corsOptions = {
  origin: process.env.REACT_APP_URL,
  credentials: true
}

async function decodeTextBody(text) {
  let buffer = Buffer.from(text, "base64");
  return Buffer.from(buffer).toString("utf8");
}
async function encodeTextBody(text) {
  let buffer = Buffer.from(text, "utf8");
  return Buffer.from(buffer).toString("base64");
}

async function myGraphqlExpressImplementation(req, res, next) {
  console.log("in express implementation");

  let gqlResponse = ""
  
  try {
    console.log("body :",req.body,Object.keys(req.body).length)
    gqlResponse = await runHttpQuery([req, res], {
      method: req.method,
      options: {
        schema: schema,
        context: { prisma }
      },
      query: req.method === "POST" ? req.body : req.query
    });
    console.log("res : ", gqlResponse)
    let type = "application/json";
    if (req.graphqlWasEncoded) {
      gqlResponse = await encodeTextBody(gqlResponse.graphqlResponse); // encoding
      type = "text/plain";
      res.setHeader("content-transfer-encoding", "base64");
    }

    res.setHeader("content-type", type);
    res.setHeader(
      "content-length",
      Buffer.byteLength(gqlResponse, "utf8").toString()
    );

    res.write(gqlResponse);
    res.end();
  } catch (error) {
    if (error.name !== "HttpQueryError") {
      return next(error);
    }
console.log("error ",error.name)
    res.setHeader("Content-Type", "application/json");
    res.setHeader(
      "Content-Length",
      Buffer.byteLength(gqlResponse, "utf8").toString()
    );
    res.write(gqlResponse);
    res.end();
  }
}
async function graphqlDecode(req, res, next) {
  console.log("decodeing", req.body, req.get("content-transfer-encoding"))
  try {
    if (req.get("content-transfer-encoding") === "base64") {
      const str = await decodeTextBody(req.body); // decoding
      req.body = JSON.parse(str);
      console.log("decoded,", req.body)
      req.headers["content-type"] = "application/json";
      req.graphqlWasEncoded = true;
      console.log("in decodeing")
    }
    else{
      console.log("body",req.get("body"))
    }
    next();
  } catch (e) {
    next(e);
  }
}

// server.start(() => console.log(`Server is running on http://localhost:4000`))
// const PORT = 4000;
const server = new ApolloServer({
  cors: false,
  schema,
  context: { prisma },
  connectToDevTools: true,
})

const app = express();
app.use(cors(corsOptions));

//app.use is the middleware
app.use(
  '/',
  require("body-parser").text(),
  graphqlDecode,
  // myGraphqlExpressImplementation
);

server.applyMiddleware({ app, cors: false,path:"/" });
app.listen({ port: 4000 }, () =>
  console.log(`🚀 Server ready at http://localhost:4000`)
);

