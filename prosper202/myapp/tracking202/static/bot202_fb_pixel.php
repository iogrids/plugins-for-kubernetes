function activateBot202PixelFBAssistant(){

      if (dcs.t202DataObj.dynamic_epv) {
    fbq('track', dcs.t202DataObj.event_type, {
            content_type : dcs.t202DataObj.content_type,
            content_name : dcs.t202DataObj.content_name+' - Click To View',
            value: dcs.t202DataObj.dynamic_epv,
            currency: dcs.t202DataObj.currency
      });
    }
    else{

      fbq('track', dcs.t202DataObj.event_type, {
            content_type : dcs.t202DataObj.content_type,
            content_name : dcs.t202DataObj.content_name+' - Click To View'
         
      });

    }

}


function activateBot202PixelFBAssistantPurchase(payout){
   fbq('track', 'Purchase', {
      content_type : dcs.t202DataObj.content_type,
      content_name : dcs.t202DataObj.content_name,
      value: payout,
      currency: dcs.t202DataObj.currency});
}