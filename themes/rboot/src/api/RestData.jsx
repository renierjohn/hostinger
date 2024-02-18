import { useState, useEffect } from 'react'
import axios from 'axios';

import { ComponentBaseData } from '../redux/store';

const RestData = (params) => {

  const BASE_DOMAIN = import.meta.env['VITE_RBOOT_DOMAIN'];

  const API_ENDPOINT = import.meta.env['VITE_RBOOT_ENDPONT'];

  const [restData, setData] = useState({});

  const [restLoading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const contextual = params.id !== undefined ? `${params.key}/${params.id}` : `${params.key}`;
        const { data: response } = await axios.get(`${BASE_DOMAIN}${API_ENDPOINT}/${contextual}`);
        const data = ComponentBaseData(response[0]);
        setData(data);
      } catch (error) {
        console.error(error)
      }
      setLoading(false);
    };
    fetchData();
  }, []);

  return {
    restData,
    restLoading,
  };
};

export default RestData;
