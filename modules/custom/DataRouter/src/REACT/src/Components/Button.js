import React , { useState } from 'react';


const button = (props) => {
	const [name,setName] = useState('');
	const [count,setCount] = useState('');
	
	function changeName(){
		setName('NEW CHILDREN');
		document.getElementsByClassName('cart')[0].innerHTML = '121212'
	}
  

  return (
    <>
	    <button onClick= {() => changeName()} >
	   		{ props.name }
	    </button>
	    <h1>{ name ? name : props.children}</h1>
    </>
  )
}

export default button;