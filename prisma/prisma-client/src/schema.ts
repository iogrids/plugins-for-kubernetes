const { makePrismaSchema, prismaObjectType } = require('nexus-prisma')
const {idArg,stringArg, arg}  = require( 'nexus')
// const {datamodelInfo}  = require('./generated/nexus-prisma')
const datamodelInfo = require('./generated/nexus-prisma')

// const { processUpload } = require('./modules/fileApi')
const path = require('path')
const { prisma }  = require( './generated/prisma-client')
var d = datamodelInfo.default

// import { GraphQLUpload } from "graphql-upload";

// import { asNexusMethod } from "nexus";

// export const Upload = asNexusMethod(GraphQLUpload, "Upload");

// import { inputObjectType } from "nexus";

// export const ProductCreateInputOverride = inputObjectType({
//   name: "ProductCreateInputOverride",
//   definition(t) {
//     t.field("images");
//   }
// });


const Query = prismaObjectType({
    name: 'Query',
    definition: (t) => {
      t.prismaFields(['*'])
    }
  })
  const Mutation = prismaObjectType({
    name: 'Mutation',
    definition: (t) =>{ 
      t.prismaFields(['*'])
      // t.field('createMypgg',{
      //   type:'Mypg',
      //   args:{
      //     data:arg({type:'MypgCreateInput'})
      //   },
      //   resolve:(parent,info,ctx)=>{
      //     console.log("info",info.data)
      //     return ctx.prisma.createMypg({"data":info.data})
      //   }
      // })
  }
  })
  
  module.exports  = makePrismaSchema({
    types: [Query, Mutation],
  
    prisma: {
      datamodelInfo:d,
      client: prisma
    },
  
    outputs: {
      schema: path.join(__dirname, './generated/schema.graphql'),
      typegen: path.join(__dirname, './generated/nexus.ts'),
    },
  })

