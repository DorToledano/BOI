import logo from '../assets/logo.jpeg'
import React from 'react';
import { Link } from 'react-router-dom';

const Nav = () => {
  return (
    <nav>
      <div className="logo-container">
        <img src={logo} alt="Bank Logo" className="logo" />
      </div>
      <div className="nav-links">
        <Link to="/" id="home-link">Home</Link>
        <Link to="/statistics" id="statistics-link">Statistics</Link>
      </div>
    </nav>
  );
};

export default Nav;
