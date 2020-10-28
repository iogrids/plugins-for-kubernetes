"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
var prisma_lib_1 = require("prisma-client-lib");
var typeDefs = require("./prisma-schema").typeDefs;

var models = [
  {
    name: "User",
    embedded: false
  },
  {
    name: "Parameter",
    embedded: false
  },
  {
    name: "DefaultParam",
    embedded: false
  },
  {
    name: "Token",
    embedded: false
  },
  {
    name: "Traffic",
    embedded: false
  },
  {
    name: "Lander",
    embedded: false
  },
  {
    name: "Offer",
    embedded: false
  },
  {
    name: "Navigation",
    embedded: false
  },
  {
    name: "Campaign",
    embedded: false
  },
  {
    name: "CampNavRows",
    embedded: false
  },
  {
    name: "Domain",
    embedded: false
  },
  {
    name: "MainDomain",
    embedded: false
  }
];
exports.Prisma = prisma_lib_1.makePrismaClientClass({
  typeDefs,
  models,
  endpoint: `http://prisma-secondary-service:4466`,
  secret: `MuchSecret`
});
exports.prisma = new exports.Prisma();
