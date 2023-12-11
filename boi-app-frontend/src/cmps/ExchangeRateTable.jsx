
import React from 'react';

const ExchangeRateTable = ({ exchangeRate }) => (
  <section className="exchange-rate-table-container">
    <table className="exchange-rate-table">
      <thead>
        <tr>
          <th>Currency</th>
          <th>Exchange Rate</th>
          <th>Date </th>
        </tr>
      </thead>
      <tbody>
        {exchangeRate.map((rate) => (
          <tr key={rate.id}>
            <td>{rate.currency}</td>
            <td>{rate.exchange_rate}</td>
            <td>{rate.Date_stamp}</td>
          </tr>
        ))}
      </tbody>
    </table>
  </section>
);

export default ExchangeRateTable;
