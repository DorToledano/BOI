
import React from 'react';

const CurrencyDropdown = ({ value, onChange, options }) => (
  <div className="currency-selector">
    <label htmlFor="currency">Select Currency:</label>
    <select name="currency" id="currency" value={value} onChange={onChange}>
      {options.map((option) => (
        <option key={option} value={option}>
          {option}
        </option>
      ))}
    </select>
  </div>
);

export default CurrencyDropdown;
