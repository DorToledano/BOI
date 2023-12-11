
import React from 'react';
import { Line } from 'react-chartjs-2';

const LineChart = ({ labels, data, label, color }) => {
  const chartData = {
    labels: labels.slice().reverse(),
    datasets: [
      {
        label: label,
        data: data.slice().reverse(),
        borderColor: color,
        fill: false,
      },
    ],
  };

  return <Line data={chartData} />;
};

export default LineChart;
