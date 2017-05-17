import 'chart.js';
import moment from 'moment';

import Module from 'module';
export default class extends Module {
	templateUrl(){ return 'home/tab-home'; }
    getData(){
        return [
            $serviceJSON('home/tab-home','load'),
        ];
    }
    domReady(){

        var self = this;
        var data = self.data;

        var element = self.element;

        // Calculate balance Agee

        var nonEchu =  data.balance.nonEchu;
        var j30 = data.balance.j30;
        var j60 = data.balance.j60;
        var j90 = data.balance.j90;
        var j180 = data.balance.j180;
        var plus1an = data.balance.plus1an;

        var balanceAgee = new Chart( $("#balance-agee")[0], {

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

        //calculate CA
        let caLabels = data.lawCashing.month;
        let caData = data.lawCashing.details ;

        console.log(caData);
        var evolutionCa = new Chart( $("#evolution-ca"), {

            label: 'ca',
            type: 'line',
            data: {
                labels:caLabels,
                datasets: [
                    {
                        label: "CA",
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
                        data:[caData[0]['ca'],caData[1]['ca'],caData[2]['ca'],caData[3]['ca'],caData[4]['ca'],caData[5]['ca'],caData[6]['ca'],caData[7]['ca'],caData[8]['ca'],caData[9]['ca'],caData[10]['ca'],caData[11]['ca'],caData[12]['ca']],
                        spanGaps: false,
                    },


                ]

            },
            options: {
                options: {
                    scales: {
                        xAxes: [{
                            type: 'linear',
                            position: 'bottom'
                        }]
                    }
                }
            }

        });



        //calculate DSO

        var dsoLabels = data.dso.labels;
        var dsoData = data.createdso;

        var evolutionDso = new Chart( $("#evolution-dso"), {

            label: 'dso',
            type: 'line',
            data: {
                // labels: [dsoLabels[0], dsoLabels[1], dsoLabels[2], dsoLabels[3], dsoLabels[4], dsoLabels[5], dsoLabels[6],dsoLabels[7], dsoLabels[8], dsoLabels[9], dsoLabels[10], dsoLabels[11], dsoLabels[12]],
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
                    // {
                    //     label: "DSO retard",
                    //     fill: false,
                    //     lineTension: 0.1,
                    //     backgroundColor: "#FF6868",
                    //     borderColor: "#FF6868",
                    //     borderCapStyle: 'butt',
                    //     borderDash: [],
                    //     borderDashOffset: 0.0,
                    //     borderJoinStyle: 'miter',
                    //     pointBorderColor: "rgba(75,192,192,1)",
                    //     pointBackgroundColor: "#fff",
                    //     pointBorderWidth: 1,
                    //     pointHoverRadius: 5,
                    //     pointHoverBackgroundColor: "rgba(75,192,192,1)",
                    //     pointHoverBorderColor: "rgba(220,220,220,1)",
                    //     pointHoverBorderWidth: 2,
                    //     pointRadius: 1,
                    //     pointHitRadius: 10,
                    //     data: [dsoData[0]['dsoRetard'], dsoData[1]['dsoRetard'], dsoData[2]['dsoRetard'], dsoData[3]['dsoRetard'], dsoData[4]['dsoRetard'], dsoData[5]['dsoRetard'], dsoData[6]['dsoRetard'],dsoData[7]['dsoRetard'], dsoData[8]['dsoRetard'], dsoData[9]['dsoRetard'], dsoData[10]['dsoRetard'], dsoData[11]['dsoRetard'], dsoData[12]['dsoRetard']],
                    //     spanGaps: false,
                    // },
                    // {
                    //     label: "DSO contractuel 60j",
                    //     fill: false,
                    //     lineTension: 0.1,
                    //     backgroundColor: "#4970FF",
                    //     borderColor: "#4970FF",
                    //     borderCapStyle: 'butt',
                    //     borderDash: [],
                    //     borderDashOffset: 0.0,
                    //     borderJoinStyle: 'miter',
                    //     pointBorderColor: "rgba(75,192,192,1)",
                    //     pointBackgroundColor: "#fff",
                    //     pointBorderWidth: 1,
                    //     pointHoverRadius: 5,
                    //     pointHoverBackgroundColor: "rgba(75,192,192,1)",
                    //     pointHoverBorderColor: "rgba(220,220,220,1)",
                    //     pointHoverBorderWidth: 2,
                    //     pointRadius: 1,
                    //     pointHitRadius: 10,
                    //     data: [dsoData[0]['dso60'], dsoData[1]['dso60'], dsoData[2]['dso60'], dsoData[3]['dso60'], dsoData[4]['dso60'], dsoData[5]['dso60'], dsoData[6]['dso60'],dsoData[7]['dso60'], dsoData[8]['dso60'], dsoData[9]['dso60'], dsoData[10]['dso60'], dsoData[11]['dso60'], dsoData[12]['dso60']],
                    //     spanGaps: false,
                    // },

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

        let promesses = data.pieces.promesses.replace(/,/g, ".").replace(/ /g, "");
        let litiges = data.pieces.litiges.replace(/,/g, ".").replace(/ /g, "");
        let echeances = data.pieces.echeances.replace(/,/g, ".").replace(/ /g, "");



        var statutPieces = new Chart( $("#statut-pieces"), {
            label: 'statut des piéces',
            type: 'doughnut',
            data: {

                labels:["promesses de regl.","litiges","echeances"],
                datasets: [
                    {
                        data: [promesses,litiges,echeances],
                        backgroundColor: [
                            "#FF6384",
                            "#36A2EB",
                            "#ff722e"
                        ],
                        hoverBackgroundColor: [
                            "#FF6384",
                            "#36A2EB",
                            "#ff722e"
                        ]
                    }]

            },
            options: {
                animation:{
                    animateScale:true
                },


            },

        });

    }
};
