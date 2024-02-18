import { useState, useEffect } from 'react'
import axios from 'axios';

import { ComponentBaseData } from '../redux/store';

const RestNodeData = (params) => {

  const BASE_DOMAIN = import.meta.env['VITE_RBOOT_DOMAIN'];

  const API_ENDPOINT = import.meta.env['VITE_RBOOT_NODE_ENDPOINT'];

  const [restNodeData, setData] = useState({});

  const [restNodeLoading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const { data: response } = await axios.get(`${BASE_DOMAIN}${API_ENDPOINT}/${params.uuid}`);
        const data = response;
        setData(data);
      } catch (error) {
        console.error(error)
      }
      setLoading(false);
    };
    fetchData();
  }, []);

  return {
    restNodeData,
    restNodeLoading,
  };
};

export default RestNodeData;
