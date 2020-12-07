protected void SyncOrderReport(string STime, string ETime, string id)
        {
            #region 异步处理数据
            var ta = new System.Threading.Tasks.Task(() =>
            {
                Hashtable ht = new Hashtable();
                var orderBll = (new DealerLadeInfoBusinessFactory()).CreateDealerLadeInfoBusiness();//平台订单

                var list = new List<DealerLadeInfo>();
                if (STime.IsNotNullAndEmpty() && ETime.IsNotNullAndEmpty())
                {
                    ht.Add(DealerLadeInfoHash.CustomWhere, "DLI_LadeDate>='" + STime.ToDateTime() + "' and DLI_LadeDate<='" + ETime.ToDateTime() + "'");
                    list = orderBll.GetAllDealerLadeInfo(ht).ToList();
                    Function.WriteDebugLog($"同步厂发报表数据 - 开始时间：{STime}，结束时间：{ETime}，查询到数据：{list.Count}条");
                }
                if (id.IsNotNullAndEmpty())
                {
                    var ot = orderBll.GetById(id);
                    if (ot.IsNotNull())
                    {
                        list.Add(ot);
                    }
                    Function.WriteDebugLog($"同步厂发报表数据 - 平台订单ID：{id}");
                }
                //对应时间范围的采购订单 (平台订单提货时间如有修改可能找不到对应采购订单)
                //var pobll = (new PurchaseOrderBusinessFactory()).CreatePurchaseOrderBusiness();//采购订单
                //ht.Clear();
                //ht.Add(PurchaseOrderHash.CustomWhere, "PO_Status>0 and PO_OutTime>='" + args.STime.ToDateTime() + "' and PO_OutTime<='" + args.ETime.ToDateTime() + "'");
                //var polist = pobll.GetAllPurchaseOrder(ht);

                var insertObjs = new List<Models.DealerLadeReport>();
                if (list.Count > 0)
                {
                    foreach (var item in list)
                    {
                        var model = new Models.DealerLadeReport();
                        model.Id = item.Id;
                        model.LadeDate = item.LadeDate;
                        model.CId = item.SellerId;
                        model.CName = item.SellerComp;
                        model.ProductId = item.CI_Id;
                        model.ProductName = item.BName + item.KName + item.Packing;
                        model.SupplierId = item.SupplierId.ToLong();
                        model.SupplierName = item.SupplierName;
                        model.LogisticId = item.LogisticId.ToLong();
                        model.LogisticName = item.LogisticName;
                        model.BuyerId = item.BuyerId;
                        model.BuyerName = Utilitys.GetCustomerFilesByUId(item.SellerId, item.BuyerId).ShortName;
                        model.SalesNum = item.Num;

                        //@todo >>>>>>>>>>>>>>> GetWaybillById --根据平台订单获取运单数据
                        var wl = GetWaybillById(item.Id);
                        model.WaybillNum = wl.WaybillNum;//获取订单下的全部运单已送达的数量


                        model.SalesFactNum = item.FactNum;
                        model.SalesPrice = item.Price;
                        model.SalesFactPrice = item.FactPrice;

                        //@todo >>>>>>>>>>>>>>> GetContractById --  根据平台订单获取销售合同相关数据
                        var mm = GetContractById(item.Id, item.CI_Id, item.BuyerId, item.OrderId, item.LogisticId);
                        model.SCId = mm.SCId;//销售合同关联数据
                        model.SCNo = mm.SCNo;
                        model.POId = item.Id;//目前平台订单ID=采购订单ID
                        model.PCId = mm.PCId;//采购合同关联数据
                        model.PCNo = mm.PCNo;
                        model.SCCarryType = (short)mm.CarryType;
                        model.SCSaleType = (short)mm.SaleType;
                        model.SCCountryName = mm.CountryName.IsNull() ? "" : mm.CountryName;
                        model.SCTaxRate = mm.TaxRate;
                        model.LRI_Id = mm.LRI_Id;
                        model.SCProjectName = mm.ProjectName;
                        model.POFactNum = mm.POFactNum;
                        model.PCTaxRate = mm.PCTaxRate;
                        model.POFactPrice = mm.POFactPrice;
                        model.POPrice = mm.POPrice;
                        model.CCTaxRate = mm.CCTaxRate;
                        model.FactFreight = wl.FactFreight;
                        model.Freight = wl.Freight;
                        model.Status = item.Status;
                        model.Province = mm.Province;
                        model.ProvinceCode = mm.ProvinceCode;
                        model.City = mm.City;
                        model.CityCode = mm.CityCode;
                        model.Address = mm.Address;
                        model.Area = mm.Area;
                        model.AreaCode = mm.AreaCode;
                        model.SCModeType = (short)mm.SCModeType;

                        model.KId = item.KId;
                        model.BId = item.BId;
                        model.Packing = item.Packing;

                        //@todo >>>>>>>>>>>>>>>GetQuotesPrice
                        model.QuotesPrice = GetQuotesPrice(item.CI_Id, item.LadeDate, 1);

                        insertObjs.Add(model);
                    }


                    var Db = new DbContext<Models.DealerLadeReport>().Db;
                    var s9 = Db.Saveable<Models.DealerLadeReport>(insertObjs).ExecuteReturnEntity();
                }
            });
            ta.Start();
            #endregion
        }






        /// <summary>
        /// 根据平台订单获取运单数据
        /// </summary>
        /// <param name="Id"></param>
        /// <returns></returns>
        protected WaybillByIdResult GetWaybillById(string id){
            var model = new WaybillByIdResult();
            model.WaybillNum = 0;
            model.FactFreight = 0;
            model.Freight = 0;
            try
            {
                if (id.IsNotNullAndEmpty())
                {
                    //var resultdata = LogisticsWebApi.WebApiPostV2<GetWaybillListByLO_IdArgsIn, GetWaybillByIdResult>("/api/Order/Inside_GetWaybillById", new GetWaybillListByLO_IdArgsIn()
                    //{
                    //    LO_Id = id
                    //});
                    var resultdata = LogisticsWebApi.WebApiPostPHP<BankCement.Logistics.IO.YDOrder.YdOrderDataArgs, BankCement.Logistics.IO.YDOrder.YdOrderDataResult>("/saas/logistics/api/stat/inGetYdOrderData", new BankCement.Logistics.IO.YDOrder.YdOrderDataArgs()
                    {
                        OrderId = id
                    });
                    if (resultdata.IsNotNull())
                    {
                        if (resultdata.Code == 200)
                        {
                            model.WaybillNum = resultdata.Data.WaybillNum.ToDecimal(); //预估数量
                            model.FactFreight = resultdata.Data.FactFreight.ToDecimal();  //入账运费
                            model.Freight = resultdata.Data.Freight.ToDecimal();//运费
                        }
                    }
                }
            }
            catch (Exception ex)
            {
                Function.WriteErrorLog(ex);
            }
            return model;
        }



/// <summary>
        /// 根据平台订单获取销售合同相关数据
        /// </summary>
        /// <param name="id"></param>
        /// <returns></returns>

        protected GetContractByIdResult GetContractById(
                string id --业务订单ID,
                long ciid --  关联子公司产品库ID,
                string userid, -- 采购商用户ID(水泥网)
                string oid, -- 关联经销商销售订单ID
                string logisticId -- 物流商ID
         ){

            var model = new GetContractByIdResult();
            model.SCId = 0;
            model.PCId = 0;
            model.POFactNum = null;
            model.POFactPrice = null;
            model.POPrice = 0;
            model.PCNo = string.Empty;
            model.PCTaxRate = null;
            model.SCNo = string.Empty;
            model.CarryType = 0;
            model.SaleType = 0;
            model.CountryName = string.Empty;
            model.TaxRate = null;
            model.ProjectName = string.Empty;
            model.CCTaxRate = null;
            model.Province = string.Empty;
            model.ProvinceCode = 0;
            model.City = string.Empty;
            model.CityCode = 0;
            model.Address = string.Empty;
            model.Area = string.Empty;
            model.AreaCode = 0;
            model.SCModeType = 0;
            try
            {
                if (id.IsNotNullAndEmpty())
                {
                    var scbll = (new SalesContractBusinessFactory()).CreateSalesContractBusiness();//销售合同
                    var pcbll = (new PurchaseContractBusinessFactory().CreatePurchaseContractBusiness());//采购合同
                    var ccbll = (new CarriageContractBusinessFactory().CreateCarriageContractBusiness());//运输合同
                    var pobll = (new PurchaseOrderBusinessFactory()).CreatePurchaseOrderBusiness();//采购订单

                    Hashtable ht = new Hashtable();
                    ht.Add(PurchaseOrderHash.SourceId, id);
                    ht.Add(PurchaseOrderHash.CustomWhere, "PO_Status>0");
                    var po = pobll.GetAllPurchaseOrder(ht).FirstOrDefault();
                    if (po.IsNotNull())
                    {
                        model.SCId = po.SCId;
                        model.PCId = po.PCId;

                        model.POFactNum = po.FactNum;
                        model.POFactPrice = po.AccountedPrice;
                        model.POPrice = po.PurchasePrice;
                        //采购合同存在
                        if (po.PCId > 0)
                        {
                            var pc = pcbll.GetById(po.PCId);
                            if (pc.IsNotNull())
                            {
                                model.PCNo = pc.ContractNo;
                                model.PCTaxRate = pc.TaxRate;
                            }
                        }
                    }
                    //获取对应物流合同
                    if (ciid > 0 && userid.IsNotNullAndEmpty())
                    {
                        ht.Clear();
                        // 读取订单收货地址
                        var lppbll = (new LadePassPointBusinessFactory().CreateLadePassPointBusiness());
                        ht.Add(LadePassPointHash.LO_Id, id);
                        LadePassPoint lppModel = lppbll.GetAllLadePassPoint(ht).FirstOrDefault();
                        if (lppModel.IsNotNull())
                        {
                            // 获取物流运费条件 - 收货地址id
                            var addressid = lppModel.LV_Id.ToLong();
                            model.Province = lppModel.Province;
                            model.ProvinceCode = lppModel.ProvinceCode;
                            model.City = lppModel.City;
                            model.CityCode = lppModel.CityCode;
                            model.Area = lppModel.Area;
                            model.AreaCode = lppModel.AreaCode;
                            model.Address = lppModel.Name;
                            model.LRI_Id = addressid;
                            var lrbll = (new LadeReceivingInfoBusinessFactory().CreateLadeReceivingInfoBusiness());
                            var lrmo = lrbll.GetById(addressid);
                            if (lrmo.IsNotNull())
                            {
                                model.ProjectName = lrmo.Name;//经销商地址名称
                                //if (oid.IsNotNullAndEmpty())//判断是否是下级用户下的订单
                                //{
                                //    if (lrmo.LriId.IsNotNullAndEmpty())
                                //    {
                                //        addressid = lrmo.LriId.ToLong();//经销商下级客户地址
                                //        var userLri = lrbll.GetById(addressid);
                                //        if (userLri.IsNotNull())
                                //        {
                                //            model.ProjectName = userLri.Name;//下级客户地址名称
                                //        }
                                //    }
                                //}
                            }

                            if (addressid > 0)
                            {
                                if (model.SCId <= 0)
                                {
                                    // 获取直营订单对应的执行合同关系
                                    var dlcbll = (new DealerLadeContractBusinessFactory().CreateDealerLadeContractBusiness());
                                    ht.Clear();
                                    ht.Add(DealerLadeContractHash.DLI_Id, id);
                                    var dlc = dlcbll.GetAllDealerLadeContract(ht).FirstOrDefault();
                                    if (dlc.IsNotNull())
                                    {
                                        var scModel = scbll.GetById(dlc.SC_Id);
                                        // 获取有效的 执行合同
                                        if (scModel.IsNotNull())
                                        {
                                            //2020-04-28 临时合同已完成 ，正式合同已签署
                                            if ((scModel.Status == 2 && scModel.Category == 1) || (scModel.Status == 8 && scModel.Category > 1))
                                            {
                                                model.SCId = dlc.SC_Id;//销售合同ID
                                            }
                                        }
                                    }
                                }
                                if (model.SCId > 0)
                                {
                                    // 根据有效的 执行合同id在《运输合同与执行合同关系表》里取运输合同相关数据
                                    var csbll = (new CarriageSaleContractBusinessFactory().CreateCarriageSaleContractBusiness());
                                    ht.Clear();
                                    ht.Add(CarriageSaleContractHash.SCId, model.SCId);
                                    ht.Add(CarriageSaleContractHash.State, 2);
                                    IList<CarriageSaleContract> cscList = csbll.GetAllCarriageSaleContract(ht);
                                    var ccdbll = (new CarriageContractDetailBusinessFactory().CreateCarriageContractDetailBusiness());
                                    if (cscList.IsNotNull() && cscList.Count > 0)
                                    {
                                        // 遍历查找有效的物流合同
                                        foreach (CarriageSaleContract item in cscList)
                                        {
                                            if (item.CCId > 0)
                                            {
                                                var ccModel = ccbll.GetById(item.CCId);
                                                var iswuliu = false;
                                                if (ccModel.Type == 5)
                                                {
                                                    var cclbll = (new CarriageContractLinkBusinessFactory()).CreateCarriageContractLinkBusiness();
                                                    Hashtable hb = new Hashtable();
                                                    hb.Add(CarriageContractLinkHash.CCId, ccModel.Id);
                                                    ht.Add(CarriageContractLinkHash.Status, 2);
                                                    var ccllist = cclbll.GetAllCarriageContractLink(ht);
                                                    foreach (var tt in ccllist)
                                                    {
                                                        var ccinfo = ccbll.GetById(tt.LinkId);
                                                        if (ccinfo.CarrierId == logisticId)//承运方=物流商
                                                        {
                                                            iswuliu = true;
                                                            break;
                                                        }
                                                    }
                                                }
                                                else
                                                {
                                                    if (ccModel.CarrierId == logisticId)//承运方=物流商
                                                    {
                                                        iswuliu = true;
                                                    }
                                                }
                                                if (ccModel.IsNotNull() && ccModel.Status == 8 && iswuliu)
                                                {
                                                    // 根据查询到的物流合同里面查找有效的运费
                                                    // 条件： 产品id，买家id，地址id
                                                    ht.Clear();
                                                    ht.Add(CarriageContractDetailHash.CCId, item.CCId);
                                                    ht.Add(CarriageContractDetailHash.State, 2);
                                                    ht.Add(CarriageContractDetailHash.SPId, ciid);
                                                    ht.Add(CarriageContractDetailHash.UserId, userid);
                                                    ht.Add(CarriageContractDetailHash.LRI_Id, addressid);
                                                    CarriageContractDetail ccdModel = ccdbll.GetAllCarriageContractDetail(ht).FirstOrDefault();

                                                    // 获取对应物流合同的税率
                                                    if (ccdModel.IsNotNull())
                                                    {
                                                        model.CCTaxRate = ccModel.TaxRate;
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    //销售合同存在
                    if (model.SCId > 0)
                    {
                        var sc = scbll.GetById(model.SCId);
                        if (sc.IsNotNull())
                        {
                            model.SCNo = sc.ContractNo;
                            model.CarryType = sc.CarryType.ToInt();
                            model.SaleType = sc.SaleType;
                            model.CountryName = sc.CountryName;
                            model.TaxRate = sc.TaxRate;
                            //model.ProjectName = sc.ProjectName;
                            model.SCModeType = sc.ModeType;
                        }
                    }
                }
            }
            catch (Exception ex)
            {
                Function.WriteErrorLog(ex);
            }
            return model;
        }