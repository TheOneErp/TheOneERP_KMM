@extends('layouts.default')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<div id="app" class="col-12">
  <h3>年度選擇</h3>
  <input
  type="number"
  class="form-control"
  v-model.number="selectedYear"
  @change="fetchData"
/>
 <div class="row">
  <div class="col-md-6">
    <h3>今年度客戶訂單報表</h3>
    <div id="bar-chart1" style="width: 200%; height: 400px;"></div>
  </div>
  <div class="col-md-6">
    <h3>去年度客戶訂單報表</h3>
    <div id="past-bar-chart1" style="width: 200%; height: 400px;"></div>
  </div>
</div> 
<div class="row">
  <div class="col-md-6">
    <h3>今年度銷售單報表</h3>
    <div id="bar-chart2" style="width: 200%; height: 400px;"></div>
  </div>
  <div class="col-md-6">
    <h3>去年度銷售單報表</h3>
    <div id="past-bar-chart2" style="width: 200%; height: 400px;"></div>
  </div>
</div>
<div class="row">
  <div class="col-md-6">
    <h3>今年費用支出</h3>
    <div id="pie-chart" style="width: 250%; height: 400px;"></div>
  </div>
  <div class="col-md-6">
    <h3>去年費用支出</h3>
    <div id="pie-chart2" style="width: 250%; height: 400px;"></div>
  </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/v-charts/lib/style.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
  new Vue({
    el: '#app',
    data: function () {
      const currentYear = new Date().getFullYear();
      return {
        selectedYear: currentYear,
        orderData: {
          columns: ['月份', '訂單數'],
          rows: []
        },
        past_orderData: {
          columns: ['月份', '訂單數'],
          rows: []
        },
        shipmentData: {
          columns: ['月份', '銷售數', '銷售金額'],
          rows: []
        },
        past_shipmentData: {
          columns: ['月份', '銷售數', '銷售金額'],
          rows: []
        },
        costData: {
          columns: ['項目', '金額'],
          rows: []
        },
        lastMonthCostData: {
          columns: ['項目', '金額'],
          rows: []
        },
        lastClicked: null,
        pieChart: null
      };
    },
    mounted() {
      this.fetchData();
    },
    methods: {
      createBarChart1() {
        const chartDom = document.getElementById('bar-chart1');
        const myChart = echarts.init(chartDom);
        const option = {
          color: ['#5998C5'],
          tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'shadow' }
          },
          xAxis: {
            type: 'category',
            data: this.orderData.rows.map(row => row['月份']),
            name: '月份',
            nameLocation: 'center',
            nameTextStyle: {
              fontSize: 14,
              fontWeight: 'bold',
              padding: [20, 20, 40, 20]
            }
          },
          yAxis: {
            type: 'value',
            name: '訂單數',
          },
          series: [{
            data: this.orderData.rows.map(row => row['訂單數']),
            type: 'bar',
            name: '訂單數',
          }]
        };
        myChart.setOption(option);
      },
      createPastBarChart1() {
        const chartDom = document.getElementById('past-bar-chart1');
        const myChart = echarts.init(chartDom);
        const option = {
          color: ['#5998C5'],
          tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'shadow' }
          },
          xAxis: {
            type: 'category',
            data: this.past_orderData.rows.map(row => row['月份']),
            name: '月份',
            nameLocation: 'center',
            nameTextStyle: {
              fontSize: 14,
              fontWeight: 'bold',
              padding: [20, 20, 40, 20]
            }
          },
          yAxis: {
            type: 'value',
            name: '訂單數',
          },
          series: [{
            data: this.past_orderData.rows.map(row => row['訂單數']),
            type: 'bar',
            name: '訂單數',
          }]
        };
        myChart.setOption(option);
      },
      createBarChart2() {
        const chartDom = document.getElementById('bar-chart2');
        const myChart = echarts.init(chartDom);
        const option = {
          color: ['#5FBFA2', '#FAC075'],
          tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'shadow' }
          },
          legend: {
              data: ['銷售數', '銷售金額'],
              top: '8%',           // 往上拉一點（可調整 5% ~ 12%）
              padding: [10, 0, 25, 0],   // 上、右、下、左  ← 重點是第3個值（下方間距）
              itemGap: 25,         // 兩個圖例之間的水平間距
              textStyle: {
                  fontSize: 12,
                  fontWeight: 'bold'
              }
          },
          xAxis: {
            type: 'category',
            data: this.shipmentData.rows.map(row => row['月份']),
            name: '月份',
            nameLocation: 'center',
            nameTextStyle: {
              fontSize: 14,
              fontWeight: 'bold',
              padding: [20, 20, 40, 20]
            }
          },
          yAxis: [
            { type: 'value', name: '銷售數' },
            { type: 'value', name: '銷售金額', axisLabel: { formatter: '{value}' } }
          ],
          series: [
            { name: '銷售數', type: 'bar', fontSize: 10,data: this.shipmentData.rows.map(row => row['銷售數']) },
            { name: '銷售金額', type: 'bar', fontSize: 10,yAxisIndex: 1, data: this.shipmentData.rows.map(row => row['銷售金額']) }
          ]
        };
        myChart.setOption(option);
      },
      createPastBarChart2() {
        const chartDom = document.getElementById('past-bar-chart2');
        const myChart = echarts.init(chartDom);
        const option = {
          color: ['#5FBFA2', '#FAC075'],
          tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'shadow' }
          },
          legend: {
              data: ['銷售數', '銷售金額'],
              top: '8%',           // 往上拉一點（可調整 5% ~ 12%）
              padding: [10, 0, 25, 0],   // 上、右、下、左  ← 重點是第3個值（下方間距）
              itemGap: 25,         // 兩個圖例之間的水平間距
              textStyle: {
                  fontSize: 12,
                  fontWeight: 'bold'
              }
          },
          xAxis: {
            type: 'category',
            data: this.past_shipmentData.rows.map(row => row['月份']),
            name: '月份',
            nameLocation: 'center',
            nameTextStyle: {
              fontSize: 14,
              fontWeight: 'bold',
              padding: [20, 20, 40, 20]
            }
          },
          yAxis: [
            
            { type: 'value', name: '銷售數' },
            { type: 'value', name: '銷售金額', axisLabel: { formatter: '{value}' } }
          ],
          series: [
            
            { name: '銷售數', type: 'bar', fontSize: 10,data: this.past_shipmentData.rows.map(row => row['銷售數']) },
            { name: '銷售金額', type: 'bar', fontSize: 10,yAxisIndex: 1, data: this.past_shipmentData.rows.map(row => row['銷售金額']) }
            
          ]
        };
        myChart.setOption(option);
      },
      createPieChart() {
        const chartDom = document.getElementById('pie-chart');
        this.pieChart = echarts.init(chartDom);
        const option = {
          tooltip: { trigger: 'item' },
          legend: { top: '5%', left: 'center' },
          series: [{
            name: '項目',
            type: 'pie',
            radius: ['40%', '70%'],
            itemStyle: { borderRadius: 10, borderColor: '#fff', borderWidth: 2 },
            label: { show: false, position: 'center' },
            emphasis: {
              label: {
                show: true,
                formatter: '{b}: {d}%',
                position: 'inside',
                fontSize: 15,
                fontWeight: 'bold'
              }
            },
            data: this.costData.rows.map(row => ({ value: row['金額'], name: row['項目'] }))
          }]
        };
        this.pieChart.setOption(option);
      },
      createLastMonthPieChart() {
        const chartDom = document.getElementById('pie-chart2');
        this.lastMonthPieChart = echarts.init(chartDom);
        const option = {
          tooltip: {
            trigger: 'item',
            position: function (point, params, dom, rect, size) {
              var x = point[0];
              var y = point[1];
              var tooltipHeight = size.contentSize[1];
              if (y + tooltipHeight > size.viewSize[1]) {
                y -= tooltipHeight;
              }
              return [x, y];
            },
          },
          legend: {
            top: '0%',
            left: 'center'
          },
          series: [{
            name: '項目',
            type: 'pie',
            radius: ['40%', '70%'],
            itemStyle: { borderRadius: 10, borderColor: '#fff', borderWidth: 2 },
            label: { show: false, position: 'center' },
            itemStyle: {
              borderRadius: 10,
              borderColor: '#fff',
              borderWidth: 2
            },
            label: {
              show: false,
              position: 'center'
            },
            emphasis: {
              label: {
                show: true,
                formatter: '{b}: {d}%',
                position: 'inside',
                fontSize: 15,
                fontWeight: 'bold'
              }
            },
            labelLine: {
              show: true
            },
            data: this.lastMonthCostData.rows.map(item => ({
              value: item['金額'],
              name: item['項目']
            })),
          }]
        };
        this.lastMonthPieChart.setOption(option);
        this.lastMonthPieChart.on('click', (params) => {
          if (this.lastClicked && this.lastClicked.seriesIndex === params.seriesIndex &&
            this.lastClicked.dataIndex === params.dataIndex) {
            this.lastMonthPieChart.dispatchAction({ type: 'hideTip' });
            this.lastClicked = null;
          } else {
            this.lastMonthPieChart.dispatchAction({
              type: 'showTip',
              seriesIndex: params.seriesIndex,
              dataIndex: params.dataIndex
            });
            this.lastClicked = params;
          }
        });
      },
      fetchData() {
        const baseURL = "{{url('')}}/";

        // Fetch order data
        axios.post(`${baseURL}api/inject/getorderdata`, { year: this.selectedYear })
          .then(response => {
            const data = response.data;
            this.orderData.rows = data.months.map((month, index) => ({
              '月份': month,
              '訂單數': data.orderCounts[index]
            }));
            this.createBarChart1();
          }).catch(error => {
            console.error("Error fetching order data:", error);
            this.orderData.rows = []; // Set to blank data if error
            this.createBarChart1();
          });

          axios.post(`${baseURL}api/inject/getorderdata`, { year: this.selectedYear-1 })
          .then(response => {
            const data = response.data;
            this.past_orderData.rows = data.months.map((month, index) => ({
              '月份': month,
              '訂單數': data.orderCounts[index]
            }));
            this.createPastBarChart1();
          }).catch(error => {
            console.error("Error fetching order data:", error);
            this.past_orderData.rows = []; // Set to blank data if error
            this.createPastBarChart1();
          });

        // Fetch shipment data
        axios.post(`${baseURL}api/inject/getShipmentData`, { year: this.selectedYear })
          .then(response => {
            const data = response.data;
            this.shipmentData.rows = data.months.map((month, index) => ({
              '月份': month,
              '銷售數': data.shipmentCounts[index],
              '銷售金額': data.shipmentAmounts[index]
            }));
            this.createBarChart2();
          }).catch(error => {
            console.error("Error fetching shipment data:", error);
            this.shipmentData.rows = []; // Set to blank data if error
            this.createBarChart2();
          });

          axios.post(`${baseURL}api/inject/getShipmentData`, { year: this.selectedYear-1 })
          .then(response => {
            const data = response.data;
            this.past_shipmentData.rows = data.months.map((month, index) => ({
              '月份': month,
              '銷售數': data.shipmentCounts[index],
              '銷售金額': data.shipmentAmounts[index]
            }));
            this.createPastBarChart2();
          }).catch(error => {
            console.error("Error fetching shipment data:", error);
            this.past_shipmentData.rows = []; // Set to blank data if error
            this.createPastBarChart2();
          });

        // Fetch cost data
        axios.post(`${baseURL}api/inject/getExpenseData`, { year: this.selectedYear })
          .then(response => {
            if (typeof response.data === 'object' && !Array.isArray(response.data)) {
              const dataArray = Object.keys(response.data).map(key => response.data[key]);
              this.costData.rows = dataArray.map(item => ({
                '項目': item.項目,
                '金額': item.金額
              }));
            } else if (Array.isArray(response.data)) {
              this.costData.rows = response.data.map(item => ({
                '項目': item.項目,
                '金額': item.金額
              }));
            } else {
              console.error("Invalid data format for cost data");
              this.costData.rows = []; // Set to blank data if invalid format
            }
            this.createPieChart();
          }).catch(error => {
            console.error("Error fetching cost data:", error);
            this.costData.rows = []; // Set to blank data if error
            this.createPieChart();
          });

        // Fetch last month cost data
        axios.post(`${baseURL}api/inject/getLastMonthExpenseData`, { year: this.selectedYear })
          .then(response => {
            if (typeof response.data === 'object' && !Array.isArray(response.data)) {
              const dataArray = Object.keys(response.data).map(key => response.data[key]);
              this.lastMonthCostData.rows = dataArray.map(item => ({
                '項目': item.項目,
                '金額': item.金額
              }));
            } else if (Array.isArray(response.data)) {
              this.lastMonthCostData.rows = response.data.map(item => ({
                '項目': item.項目,
                '金額': item.金額
              }));
            } else {
              console.error("Invalid data format for last month cost data");
              this.lastMonthCostData.rows = []; // Set to blank data if invalid format
            }
            this.createLastMonthPieChart();
          }).catch(error => {
            console.error("Error fetching last month cost data:", error);
            this.lastMonthCostData.rows = []; // Set to blank data if error
            this.createLastMonthPieChart();
          });
      }
    }
  });
</script>
@endsection
