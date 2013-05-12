<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["idx","bid","boardtitle","postdelete","width","repto","name","content","reg_date","file"]
		}),
		remoteSort:true,
		sortInfo:{field:"idx",direction:"DESC"},
		baseParams:{action:"ment",key:"",keyword:"",category:"",bid:""}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"댓글관리",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				layout:"fit",
				border:false,
				autoScroll:true,
				tbar:[
					new Ext.form.ComboBox({
						id:"BoardID",
						typeAhead:true,
						triggerAction:"all",
						lazyRender:true,
						store:new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["bid","title","option"]
							}),
							remoteSort:false,
							sortInfo:{field:"bid",direction:"ASC"},
							baseParams:{"action":"list","is_all":"true"}
						}),
						width:120,
						editable:false,
						mode:"local",
						displayField:"title",
						valueField:"bid",
						emptyText:"게시판명",
						listeners:{
							render:{fn:function() {
								Ext.getCmp("BoardID").getStore().load();
							}},
							select:{fn:function(form,selected) {
								var temp = selected.get("option").split(",");
								store.baseParams.bid = form.getValue();
								store.load({params:{start:0,limit:30}});
							}}
						}
					}),
					' ',
					new Ext.form.ComboBox({
						id:"Key",
						typeAhead:true,
						triggerAction:"all",
						lazyRender:true,
						store:new Ext.data.SimpleStore({
							fields:["keytype","text"],
							data:[["content","내용"],["name","작성자"],["ip","아이피"]]
						}),
						width:80,
						editable:false,
						mode:"local",
						displayField:"text",
						valueField:"keytype",
						value:"content"
					}),
					' ',
					new Ext.form.TextField({
						id:"Keyword",
						width:180,
						emptyText:"검색어를 입력하세요."
					}),
					' ',
					new Ext.Button({
						text:"검색",
						icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_magnifier.png",
						handler:function() {
							if (!Ext.getCmp("Keyword").getValue()) {
								Ext.Msg.show({title:"에러",msg:"검색어를 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
								return false;
							}
							store.baseParams.key = Ext.getCmp("Key").getValue();
							store.baseParams.keyword = Ext.getCmp("Keyword").getValue();
							store.load({params:{start:0,limit:30}});
						}
					}),
					new Ext.Button({
						text:"검색취소",
						icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_magifier_out.png",
						handler:function() {
							Ext.getCmp("Keyword").setValue("");
							store.baseParams.keyword = "";
							store.load({params:{start:0,limit:30}});
						}
					}),
					'-',
					new Ext.Button({
						text:"댓글관리",
						icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_database_table.png",
						menu:new Ext.menu.Menu({
							items:[
								new Ext.menu.Item({
									text:"댓글삭제",
									icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_table_delete.png",
									handler:function() {
										var checked = Ext.getCmp("ListPanel").selModel.getSelections();
										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"삭제할 댓글을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
											return false;
										}

										Ext.Msg.show({title:"안내",msg:"선택한 댓글을 정말 삭제하시겠습니까?<br />삭제된 댓글은 복구할 수 없습니다.",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
											if (button == "ok") {
												var idxs = new Array();
												for (var i=0, loop=checked.length;i<loop;i++) {
													idxs[i] = checked[i].get("idx");
												}
												var idx = idxs.join(",");

												Ext.Msg.wait("처리중입니다.","Please Wait...");
												Ext.Ajax.request({
													url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
													success:function() {
														Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
														Ext.getCmp("ListPanel").getStore().reload();
													},
													failure:function() {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													},
													headers:{},
													params:{"action":"ment","do":"delete","idx":idx}
												});
											}
										}});
									}
								}),
								new Ext.menu.Item({
									text:"댓글스팸차단",
									icon:"<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_table_error.png",
									handler:function() {
										var checked = Ext.getCmp("ListPanel").selModel.getSelections();
										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"삭제 및 스팸차단할 댓글을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
											return false;
										}

										Ext.Msg.show({title:"안내",msg:"선택한 댓글을 정말 차단 및 삭제하시겠습니까?<br />삭제된 댓글은 휴지통으로 이동됩니다.",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
											if (button == "ok") {
												var idxs = new Array();
												for (var i=0, loop=checked.length;i<loop;i++) {
													idxs[i] = checked[i].get("idx");
												}
												var idx = idxs.join(",");

												Ext.Msg.wait("처리중입니다.","Please Wait...");
												Ext.Ajax.request({
													url:"<?php echo $_ENV['dir']; ?>/module/board/exec/Admin.do.php",
													success:function() {
														Ext.Msg.show({title:"안내",msg:"성공적으로 차단 및 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
														Ext.getCmp("ListPanel").getStore().reload();
													},
													failure:function() {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													},
													headers:{},
													params:{"action":"ment","do":"spam","idx":idx}
												});
											}
										}});
									}
								})
							]
						})
					})
				],
				cm:new Ext.grid.ColumnModel([
					{
						header:"번호",
						dataIndex:"idx",
						sortable:true,
						width:60,
						css:GridLoopNum,
						renderer:GridNumberFormat
					},{
						header:"게시판명",
						dataIndex:"boardtitle",
						sortable:false,
						width:130,
						renderer:function(value,p,record) {
							return value+'<span style="font-family:tahoma;">('+record.data.bid+')</span>';
						}
					},{
						header:"댓글내용",
						dataIndex:"content",
						sortable:false,
						width:520,
						renderer:function(value,p,record) {
							var sHTML = "";
							if (record.data.postdelete == "TRUE") sHTML+= '<span style="color:#99BBE8;">[삭제된게시물]</span> ';
							sHTML+= value;

							return sHTML;
						}
					},{
						header:"첨부",
						dataIndex:"file",
						sortable:false,
						menuDisabled:true,
						width:35,
						css:"font:0/0 arial; text-align:center;",
						renderer:function(value) {
							var sHTML = "";
							if (value) {
								sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_bullet_disk.png" alt="첨부" />';
							}

							return sHTML;
						}
					},{
						header:"작성자",
						dataIndex:"name",
						sortable:true,
						width:100
					},{
						header:"작성일",
						dataIndex:"reg_date",
						sortable:true,
						width:120,
						renderer:function(value) {
							return '<div style="font-family:arial; text-align:right;">'+value+'</div>';
						}
					},
					new Ext.grid.CheckboxSelectionModel()
				]),
				store:store,
				sm:new Ext.grid.CheckboxSelectionModel(),
				bbar:new Ext.PagingToolbar({
					pageSize:30,
					store:store,
					displayInfo:true,
					displayMsg:'{0} - {1} of {2}',
					emptyMsg:"데이터없음"
				}),
				listeners:{
					cellclick:{fn:function(grid,idx,col,e) {
						if (col == 3) {
							var file = grid.getStore().getAt(idx).get("file");

							if (file) {
								var files = file.split(",");
								var menu = new Ext.menu.Menu();
								for (var i=0, total=files.length;i<total;i++) {
									var fileInfor = files[i].split("|");
									menu.add({
										text:"<span style='font-weight:bold;'>"+fileInfor[1]+"</span> <span style='font-family:tahoma; font-size:10px;'>("+GetFileSize(fileInfor[2])+", <span style='font-weight:bold;'>"+Ext.util.Format.number(fileInfor[3],"0,0")+"</span> Hits)</span>",
										icon:GetFileIcon(fileInfor[1]),
										handler:function() {
											execFrame.location.href = "<?php echo $_ENV['dir']; ?>/module/board/exec/FileDownload.do.php?idx="+fileInfor[0];
										}
									});
								}
								menu.showAt(e.getXY());
								e.stopEvent();
							}
						}
					}},
					rowdblclick:{fn:function(grid,idx,e) {
						var data = grid.getStore().getAt(idx);
						if (data.get("width").indexOf("%") != -1) {
							var width = 800;
						} else {
							var width = parseInt(data.get("width"))+10;
						}

						new Ext.Window({
							title:data.get("title"),
							width:width,
							height:500,
							layout:"fit",
							maximizable:true,
							style:"background:#FFFFFF;",
							html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/board/board.php?bid='+data.get("bid")+'&mode=view&idx='+data.get("repto")+'" style="width:100%; height:100%; background:#FFFFFF;" frameborder="0"></iframe>'
						}).show();
					}},
					rowcontextmenu:{fn:function(grid,idx,e) {
						var menu = new Ext.menu.Menu();
						var data = grid.getStore().getAt(idx);
						/*
						menu.add({
							text:"<b>"+data.get("title")+"</b>",
							icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_default.png")
						});
						menu.add(new Ext.menu.Separator({}));
						menu.add({
							text:"게시판설정",
							icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_table_gear.png"),
							handler:function(item) {
								BoardFormFunction(data.get("bid"));
							}
						});
						menu.add({
							text:"게시판바로가기",
							icon:(Ext.isIE6 ? "" : "<?php echo $_ENV['dir']; ?>/module/board/images/admin/icon_table_go.png"),
							handler:function(item) {
								new Ext.Window({
									title:data.get("title"),
									width:width,
									height:500,
									layout:"fit",
									maximizable:true,
									style:"background:#FFFFFF;",
									html:'<iframe src="<?php echo $_ENV['dir']; ?>/module/board/board.php?bid='+data.get("bid")+'" style="width:100%; height:100%; background:#FFFFFF;" frameborder="0"></iframe>'
								}).show();
							}
						});
						*/
						e.stopEvent();
						menu.showAt(e.getXY());
					}}
				}
			})
		]
	});

	store.load({params:{start:0,limit:30}});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>