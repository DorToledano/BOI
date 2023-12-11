import './styles/main.scss'
import React from 'react';
import { Route, BrowserRouter as Router, Routes } from 'react-router-dom';
import HomePage from '../src/pages/HomePage.jsx';
import Statistics from '../src/pages/Statistics.jsx';
import NavBar from './cmps/NavBar';

function App() {
  return (
    <Router>
      <section className="App">
        <NavBar />
        <main className="container">
          <Routes>
            <Route path="/statistics" element={<Statistics/>} />
            <Route path="/" exact element={<HomePage/>} />
          </Routes>
        </main>
      </section>
    </Router>
  );
}

export default App;
