import 'validate';
import 'chart.js';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'debtors/tab-analyse'; }
	getData(){
		var id = jstack.url.getParams(this.hash).id;
        this.data.id = id;
		return [
            $serviceJSON('debtors/tab-debtors-paperworks','load', [id]),

		];
	}
	domReady(){
		var self = this;
		var data = self.data;
		var element = self.element;
		var dsoLabels = data.dso.labels;
        var dsoData = data.createdso;
        var evolutionDso = new Chart( $("#analyse-evolution-dso"), {

            label: 'dso',
            type: 'line',
            data: {
                labels:dsoLabels,
                datasets: [
                    {
                        label: "DSO",
                        fill: false,
                        lineTension: 0.1,
                        backgroundColor: "rgba(75,192,192,0.4)",
                        borderColor: "rgba(75,192,192,1)",
                        borderCapStyle: 'butt',
                        borderDash: [],
                        borderDashOffset: 0.0,
                        borderJoinStyle: 'miter',
                        pointBorderColor: "rgba(75,192,192,1)",
                        pointBackgroundColor: "#fff",
                        pointBorderWidth: 1,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: "rgba(75,192,192,1)",
                        pointHoverBorderColor: "rgba(220,220,220,1)",
                        pointHoverBorderWidth: 2,
                        pointRadius: 1,
                        pointHitRadius: 10,
                        data: [dsoData[0]['dso'], dsoData[1]['dso'], dsoData[2]['dso'], dsoData[3]['dso'], dsoData[4]['dso'], dsoData[5]['dso'], dsoData[6]['dso'],dsoData[7]['dso'], dsoData[8]['dso'], dsoData[9]['dso'], dsoData[10]['dso'], dsoData[11]['dso'], dsoData[12]['dso']],
                        spanGaps: false,
                    },
                    {
                        label: "DSO retard",
                        fill: false,
                        lineTension: 0.1,
                        backgroundColor: "#FF6868",
                        borderColor: "#FF6868",
                        borderCapStyle: 'butt',
                        borderDash: [],
                        borderDashOffset: 0.0,
                        borderJoinStyle: 'miter',
                        pointBorderColor: "rgba(75,192,192,1)",
                        pointBackgroundColor: "#fff",
                        pointBorderWidth: 1,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: "rgba(75,192,192,1)",
                        pointHoverBorderColor: "rgba(220,220,220,1)",
                        pointHoverBorderWidth: 2,
                        pointRadius: 1,
                        pointHitRadius: 10,
                        data: [dsoData[0]['dsoRetard'], dsoData[1]['dsoRetard'], dsoData[2]['dsoRetard'], dsoData[3]['dsoRetard'], dsoData[4]['dsoRetard'], dsoData[5]['dsoRetard'], dsoData[6]['dsoRetard'],dsoData[7]['dsoRetard'], dsoData[8]['dsoRetard'], dsoData[9]['dsoRetard'], dsoData[10]['dsoRetard'], dsoData[11]['dsoRetard'], dsoData[12]['dsoRetard']],
                        spanGaps: false,
                    },
                    {
                        label: "DSO contractuel 60j",
                        fill: false,
                        lineTension: 0.1,
                        backgroundColor: "#4970FF",
                        borderColor: "#4970FF",
                        borderCapStyle: 'butt',
                        borderDash: [],
                        borderDashOffset: 0.0,
                        borderJoinStyle: 'miter',
                        pointBorderColor: "rgba(75,192,192,1)",
                        pointBackgroundColor: "#fff",
                        pointBorderWidth: 1,
                        pointHoverRadius: 5,
                        pointHoverBackgroundColor: "rgba(75,192,192,1)",
                        pointHoverBorderColor: "rgba(220,220,220,1)",
                        pointHoverBorderWidth: 2,
                        pointRadius: 1,
                        pointHitRadius: 10,
                        data: [dsoData[0]['dso60'], dsoData[1]['dso60'], dsoData[2]['dso60'], dsoData[3]['dso60'], dsoData[4]['dso60'], dsoData[5]['dso60'], dsoData[6]['dso60'],dsoData[7]['dso60'], dsoData[8]['dso60'], dsoData[9]['dso60'], dsoData[10]['dso60'], dsoData[11]['dso60'], dsoData[12]['dso60']],
                        spanGaps: false,
                    },

                ]

            },
            options: {
                options: {
                    scales: {
                        xAxes: [{
                            display: false
                        }]
                    }
                }
            }

        });


        var nonEchu =  data.balance.nonEchu;
        var j30 = data.balance.j30;
        var j60 = data.balance.j60;
        var j90 = data.balance.j90;
        var j180 = data.balance.j180;
        var plus1an = data.balance.plus1an;


        var myChart = new Chart( $("#analyse-balance-agee")[0], {

            label: 'balance agée en €',
            type: 'bar',
            data: {
                labels: ["Non echu", "30 J", "60 J", "90 J", "180 J", "+ 1 an"],
                datasets: [{

                    data:[ nonEchu, j30, j60, j90, j180, plus1an],
                    backgroundColor: [
                        'rgba(255,99,132,1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderColor: [
                        'rgba(255,99,132,1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 2
                }]

            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero:true
                        }
                    }]
                }
            }

        });

	}
};
