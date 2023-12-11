import axios from 'axios';

const fetchData = async (url) => {
  try {
    const response = await axios.get(url);
    return response.data;
  } catch (error) {
    console.error('Error fetching data:', error.message);
    throw error; // Re-throw the error to handle it elsewhere if needed
  }
};

export default fetchData;