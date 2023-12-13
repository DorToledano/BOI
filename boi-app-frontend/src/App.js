import './styles/main.scss'
import React, { useEffect, useState } from 'react';
import { Route, BrowserRouter as Router, Routes } from 'react-router-dom';
import HomePage from '../src/pages/HomePage.jsx';
import Statistics from '../src/pages/Statistics.jsx';
import NavBar from './cmps/NavBar';

function App() {

  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    initialData()
  }, [])

  const initialData = () => {
    fetch('http://localhost/bank-proj/index.php')
      .then(res => setIsLoading(false))
      .catch(err => console.error('fetch error here', err))
  }

  if (isLoading) return (
    <div>
      Loading...
    </div>
  )

  return (
    <Router>
      <section className="App">
        <NavBar />
        <main className="container">
          <Routes>
            <Route path="/statistics" element={<Statistics />} />
            <Route path="/" exact element={<HomePage />} />
          </Routes>
        </main>
      </section>
    </Router>
  );
}

export default App;