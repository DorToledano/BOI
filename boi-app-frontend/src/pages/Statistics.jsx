
import React, { useState, useEffect } from 'react';
import { Line } from 'react-chartjs-2';
import 'chart.js/auto';
import fetchData from '../services/data.service'

const StatisticsPage = () => {
  const [currency, setCurrency] = useState('USD');
  const [exchangeRates, setExchangeRates] = useState([]);
  const [dateStamps, setDateStamps] = useState([]);

  useEffect(() => {
    getData();
  }, [currency]);

  const getData=async ()=>{
    const data= await fetchData(`http://localhost/bank-proj/api/rate/RateService.php?currency=${currency}`)
    const rates = data.map(entry => parseFloat(entry.exchange_rate));
    const dates = data.map(entry => entry.Date_stamp);
    setExchangeRates(rates);
    setDateStamps(dates);
  }


  if (!Array.isArray(exchangeRates) || !Array.isArray(dateStamps)) return null;

  const chartData = {
    labels: dateStamps.slice().reverse(),  
    datasets: [
      {
        label: currency,
        data: exchangeRates.slice().reverse(),  
        borderColor: 'blue',
        fill: false,
      },
    ],
  };

  
  const currencyOptions = ['USD', 'EUR', 'GBP'];

  return (
    <div className="chart-container">
      <h1>2023-2024 exchange rates to ILS</h1>
      <div className="currency-selector">
        <label htmlFor="currency">Select Currency:</label>
        <select
          name="currency"
          id="currency"
          value={currency}
          onChange={(e) => setCurrency(e.target.value)}
        >
          {currencyOptions.map((option) => (
            <option key={option} value={option}>
              {option}
            </option>
          ))}
        </select>
      </div>
      <div className="chart-wrapper">
        {exchangeRates.length > 0 ? (
          <Line data={chartData} />
        ) : (
          <p>Loading data...</p>
        )}
      </div>
    </div>
  );
};

export default StatisticsPage;
