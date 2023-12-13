import React, { useState, useEffect } from 'react';
import CurrencyDropdown from '../cmps/CurrencyDropdown';
import ExchangeRateTable from '../cmps/ExchangeRateTable';
import fetchData from '../services/data.service';

const API_ENDPOINT = 'http://localhost/bank-proj/api/rate/RateService.php';

const HomePage = () => {
  const [exchangeRate, setExchangeRate] = useState(null);
  const [currency, setCurrency] = useState('USD');
  const [limit, setLimit] = useState(7);

  useEffect(() => {
    fetchDataAndHandleError();
  }, [currency, limit]);

  const fetchDataAndHandleError = async () => {
    try {
      const data = await fetchData(`${API_ENDPOINT}?currency=${currency}&limit=${limit}`);
      await updateDatabase();
      setExchangeRate(data);
    } catch (error) {
      console.error('Error:', error.message);
    }
  };

  const updateDatabase = async () => {
    try {
      const updateResponse = await fetchData('http://localhost/bank-proj/api/update-database.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ currency, limit }),
      });
      // console.log('Database update response:', updateResponse);
    } catch (error) {
      console.error('Error updating database:', error.message);
    }
  };

  if (!exchangeRate) return <div>Loading...</div>;

  if (!Array.isArray(exchangeRate)) return null;

  return (
    <div className="homepage">
      <h2>Last week's exchange Rate to ILS</h2>
      <CurrencyDropdown
        value={currency}
        onChange={(e) => setCurrency(e.target.value)}
        options={['USD', 'EUR', 'GBP']}
      />
      <ExchangeRateTable exchangeRate={exchangeRate} />
    </div>
  );
};

export default HomePage;
