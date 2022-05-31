import React,{useEffect,useState} from 'react';

const card = (props) => {
	const [views,setViews] = useState([]);
  useEffect(() => {
  	fetch('http://hostinger.dd/api/count/views?t=70,70,71,71,72,72,66,66,19,19,12,12,11,11,10,10,9,9');
  });

  return (
   	<>
      TEST
   	</>
  )
}

export default card;