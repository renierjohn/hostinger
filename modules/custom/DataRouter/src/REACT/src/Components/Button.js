import React,{useState,useEffect} from "react";
import Users  from './Users';
import Camera from './Camera';

export default function Button({title}) {
    
  const [toggle, setToggle] = useState(false);

  const users = [
   	{
   		'id' :1,
		'name':'Renier',
		'age': 27,
   	},
   	{
   		'id':2,
		'name':'JIJI',
		'age': 28,
   	}
  ];

  return (
    <section>
      <button className="primary" onClick={() => setToggle(!toggle)}>
        {toggle ? `ON ${title}` : `OFF ${title}`}
      </button>
      
      {toggle ? <Users users={users} toggle={toggle} /> : `NO USERS`}
      {toggle ? < Camera /> : 'No Camera' }
      
    </section>
  );
}