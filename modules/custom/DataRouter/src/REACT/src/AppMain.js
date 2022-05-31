import React,{useState,useEffect} from 'react';
import Card from './Components/card';

const AppMain = (props) => {
  const [loading,setLoading] = useState(true);
  
  useEffect(()=>{
    console.log('init')
  },[]);

  // setLoading(()=>{

  // })

  return (
    <>
      <Card />
      <div>APP MAIN</div>
    </>
  )
}

export default AppMain;