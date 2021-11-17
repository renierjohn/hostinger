import React,{useEffect,useState} from "react"
import API from '../API/Users'
import PropTypes from "prop-types";


export default function UserInfo(props) {
  const paragraph = document.getElementsByClassName('description')[0].innerText;
  const users = props.users
  const updateLike = 
  		{like:[
  		  		   	{
  		  		   		'id' :1,
  		  				'count':51,
  		  		   	},
  		  		   	{
  		  		   		'id':2,
  		  		   		'count':100,
  		  		   	}
  			  	]};

  const [status,setStatus] = useState(() => {
		const status = 
	  	{
	  		like:[
	  		   	{
	  		   		'id' :1,
	  				'count':50,
	  		   	},
	  		   	{
	  		   		'id':2,
	  		   		'count':100,
	  		   	}
	  		  ],
	  		comment:[
	  		  {
	  		  	'id':1,
	  		  	'text':'Comment1'
	  		  },
	  		  {
	  		  	'id':2,
	  		  	'text':'Comment2'
	  		  }
	  		]
	  	};
  	return status;
  });
 
 useEffect(()=>{
  	// console.log(status);
  	// console.log(paragraph);
  	return () => {
  		// console.log('user unmounted');
  	};
  },[status])


  return (
    <div>
	    <ul>
		    {users.map((item,index) => {
		    	return(
			    	<li key={index} >
			    		{item.name} <br/>
			    		{item.age} <br/>
				    	<input type='submit' value='like' onClick={() => setStatus({updateLike})}/>
				    	<input type='submit' value='share' />
				    </li>

		    	)
		    })}
	    </ul>
    </div>
  );
}