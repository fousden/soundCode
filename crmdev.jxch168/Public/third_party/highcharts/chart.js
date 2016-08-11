/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


//图表参数初始化函数
//xAxis_pot                         x轴数值名，必填，类型为字符串数组
//yAxis_title                        y轴名，必填，类型为字符串
//data_name                      传入的数据名数组，必填，数据名类型为字符串
//data_array                       传入的数据数组，必填，类型为数组
//unit                                  单位名，必填，鼠标划过线条显示数据的单位，类型为字符串
//title_name                        标题名，可选，类型为string，默认为图表
//subtitle_name                   副标题名，可选，类型为string，默认为空
//使用时只需将相应的参数导入，然后执行如下jquery语句，其中#id为对应的div的id
//    $(function(){
//        var chart = $('#id').highcharts(brokenline_plot(xAxis_pot,yAxis_title,data_name,data_array,unit,title_name,subtitle_name));
//        })

function brokenline_plot(xAxis_pot,yAxis_title,data_name,data_array,unit,title_name,subtitle_name){

          var title_name = arguments[5] ? arguments[5] : '图表';                                                    //判断主标题是否为空，如为空，则标题名为图表
          var subtitle_name = arguments[6] ? arguments[6] : ' ';                                               //判断副标题是否为空，副标题默认为空
          var data_sequence = [];                                                                                                   //数据数组，根据传入的数据名数组和对应的数据数组合并
          for (i in data_name){                                                                                                       //数据数组合并循环
              temp = {};
              temp.name = data_name[i];
              temp.data = data_array[i];
              data_sequence.push(temp);
          }

//折线图所需的参数
        var options = {

                title: {                                                                                                                        //标题设置
                    text: title_name,
                    x: -20 //center
                },
                subtitle: {                                                                                                                 //副标题设置
                    text: subtitle_name,
                    x: -20
                },
                xAxis: {                                                                                                                    //x轴设置
                    categories:xAxis_pot                                                                                              //x轴单位
                },
                yAxis: {                                                                                                                    //y轴设置
                    title: {                                                                                                                  //y轴标题
                        text: yAxis_title
                    },
                    plotLines: [{                                                                                                           //y轴线设置
                        value: 0,
                        width: 1,
                        color: '#808080'
                    }]
                },
                tooltip: {                                                                                                                  //鼠标滑过图表显示的设置
                    valueSuffix: unit                                                                                                   //设置单位
                },
                legend: {                                                                                                                   //图例设置
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'middle',
                    borderWidth: 0
                },
                series:data_sequence                                                                                                    //数据集
        };
               return (options);                                                                                                                //返回图表数据
}


//图表参数初始化函数
//xAxis_pot                         x轴数值名，必填，类型为字符串数组
//yAxis_title                        y轴名，必填，类型为字符串
//data_name                      传入的数据名数组，必填，数据名类型为字符串
//data_array                       传入的数据数组，必填，类型为数组
//title_name                        标题名，可选，类型为string，默认为图表
//subtitle_name                   副标题名，可选，类型为string，默认为空
//使用时只需将相应的参数导入，然后执行如下jquery语句，其中#id为对应的div的id
//    $(function(){
//        var chart = $('#id').highcharts(column_plot(xAxis_pot,yAxis_title,data_name,data_array,title_name,subtitle_name));
//        })

function column_plot(xAxis_pot,yAxis_title,data_name,data_array,title_name,subtitle_name){

          var title_name = arguments[4] ? arguments[4] : '图表';                                                    //判断主标题是否为空，如为空，则标题名为图表
          var subtitle_name = arguments[5] ? arguments[5] : ' ';                                               //判断副标题是否为空，副标题默认为空
          var data_sequence = [];                                                                                                   //数据数组，根据传入的数据名数组和对应的数据数组合并
          for (i in data_name){                                                                                                       //数据数组合并循环
              temp = {};
              temp.name = data_name[i];
              temp.data = data_array[i];
              data_sequence.push(temp);
          }

//折线图所需的参数
        var options = {

                            chart: {                                                                                                            //图标类型设置
                                type: 'column'
                                            },
                            title: {                                                                                                               //标题设置
                                text: title_name
                            },
                            subtitle: {                                                                                                        //副标题设置
                                text: subtitle_name
                            },
                            xAxis: {                                                                                                            //x轴设置
                                categories: xAxis_pot                                                                                   //x轴单位
                            },
                            yAxis: {                                                                                                             //y轴设置
                                min: 0,
                                title: {                                                                                                             //y轴标题
                                    text: yAxis_title
                                }
                            },
                            tooltip: {                                                                                                                              //鼠标滑过图表显示的设置
                                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                    '<td style="padding:0"><b>{point.y:.1f} </b></td></tr>',
                                footerFormat: '</table>',
                                shared: true,
                                useHTML: true
                            },
                            plotOptions: {
                                column: {
                                    pointPadding: 0.2,
                                    borderWidth: 0
                                }
                            },
                            series: data_sequence                                                                                   //数据集
                    };
               return (options);                                                                                                                //返回图表数据
}


//图表参数初始化函数
//xAxis_pot                         x轴数值名，必填，类型为字符串数组
//yAxis_title                        y轴名，必填，类型为字符串
//data_name                      传入的数据名数组，必填，数据名类型为字符串
//data_array                       传入的数据数组，必填，类型为数组
//title_name                        标题名，可选，类型为string，默认为图表
//subtitle_name                   副标题名，可选，类型为string，默认为空
//使用时只需将相应的参数导入，然后执行如下jquery语句，其中#id为对应的div的id
//    $(function(){
//        var chart = $('#id').highcharts(stackcolumn_plot(xAxis_pot,yAxis_title,data_name,data_array,unit,title_name,subtitle_name));
//        })

function stackcolumn_plot(xAxis_pot,yAxis_title,data_name,data_array,title_name,subtitle_name){

          var title_name = arguments[4] ? arguments[4] : '图表';                                                    //判断主标题是否为空，如为空，则标题名为图表
          var subtitle_name = arguments[5] ? arguments[5] : ' ';                                               //判断副标题是否为空，副标题默认为空
          var data_sequence = [];                                                                                                   //数据数组，根据传入的数据名数组和对应的数据数组合并
          for (i in data_name){                                                                                                       //数据数组合并循环
              temp = {};
              temp.name = data_name[i];
              temp.data = data_array[i];
              data_sequence.push(temp);
          }

//折线图所需的参数
        var options = {

                            chart: {                                                                                                    //图标类型设置
                                type: 'column'
                            },
                            title: {                                                                                                        //标题设置
                                text: title_name,
                                x: -20
                            },
                            subtitle: {                                                                                                     //副标题设置
                                text: subtitle_name,
                                x: -20
                            },
                            xAxis: {                                                                                                          //x轴设置
                                categories: xAxis_pot                                                                                    //x轴单位
                            },
                            yAxis: {                                                                                                        //y轴设置
                                min: 0,
                                title: {                                                                                                        //y轴标题
                                    text: yAxis_title
                                },
                                stackLabels: {                                                                                          //设置y轴为堆叠标签
                                    enabled: true,
                                    style: {
                                        fontWeight: 'bold',
                                        color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                                    }
                                }
                            },
                            legend: {                                                                                                       //图例设置
                                align: 'right',
                                x: -70,
                                verticalAlign: 'top',
                                y: 20,
                                floating: true,
                                backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColorSolid) || 'white',
                                borderColor: '#CCC',
                                borderWidth: 1,
                                shadow: false
                            },
                            tooltip: {                                                                                                      //鼠标滑过图表显示的设置
                                formatter: function() {
                                    return '<b>'+ this.x +'</b><br/>'+
                                        this.series.name +': '+ this.y +'<br/>'+
                                        'Total: '+ this.point.stackTotal;
                                }
                            },
                            plotOptions: {                                                                                                //图表设置
                                column: {                                                                                                    //设置为堆叠模式
                                    stacking: 'normal',
                                    dataLabels: {
                                        enabled: true,
                                        color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
                                    }
                                }
                            },
                            series: data_sequence                                                                                         //数据集
                    };
               return (options);                                                                                                                //返回图表数据
}



//图表参数初始化函数
//series_name                         x轴数值名，必填，类型为字符串数组
//data_name                      传入的数据名数组，必填，数据名类型为字符串
//data_array                       传入的数据数组，必填，类型为数组
//title_name                        标题名，可选，类型为string，默认为图表
//subtitle_name                   副标题名，可选，类型为string，默认为空
//使用时只需将相应的参数导入，然后执行如下jquery语句，其中#id为对应的div的id
//    $(function(){
//        var chart = $('#id').highcharts(pie_plot(series_name,data_name,data_array,title_name,subtitle_name));
//        })

function pie_plot(series_name,data_name,data_array,title_name,subtitle_name){

          var title_name = arguments[3] ? arguments[3] : '图表';                                                    //判断主标题是否为空，如为空，则标题名为图表
          var subtitle_name = arguments[4] ? arguments[4] : ' ';                                               //判断副标题是否为空，副标题默认为空
          var data_sequence = [];
          for (i in data_name){                                                                                                       //数据数组合并循环
              temp = [];
              temp[0] = data_name[i];
              temp[1] = data_array[i];
              data_sequence.push(temp);
          }

//折线图所需的参数
        var options = {

                            chart: {                                                                                                         //图标类型设置
                                plotBackgroundColor: null,
                                plotBorderWidth: null,
                                plotShadow: false
                            },
                            title: {                                                                                                            //标题设置
                                text: title_name,
                                x: -20
                            },
                            subtitle: {                                                                                                          //副标题设置
                                text: subtitle_name,
                                x: -20
                            },
                            tooltip: {                                                                                                           //鼠标滑过图表显示的设置
                                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                            },
                            plotOptions: {                                                                                                   //图表设置
                                pie: {                                                                                                            //设置为饼图
                                    allowPointSelect: true,
                                    cursor: 'pointer',
                                    dataLabels: {
                                        enabled: true,
                                        color: '#000000',
                                        connectorColor: '#000000',
                                        format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                                    }
                                }
                            },
                            series: [{                                                                                                           //数据集
                                type: 'pie',
                                name: series_name,
                                data: data_sequence
                            }]
                    }
               return (options);                                                                                                                //返回图表数据
}