import React,{useState,useReducer} from "react"

export default function Input(props) {
	const initialCount = 0;

    function init(initialCount) {
      return {count: initialCount};
    }

    const [title,setState] = useState(() => {
        const initVal = 'initalValue';
        return initVal;
   });

    const [state, dispatch] = useReducer(reducer,initialCount, init);

    function reducer(state, action) {
          switch (action.type) {
            case 'increment':
              return {count: state.count + 1};
            case 'decrement':
              return {count: state.count - 1};
            case 'reset':
              return init(action.payload);
            default:
              throw new Error();
          }
    }

    return (
    	<div>
            <h3>{title}</h3>
    		<input type="text" name="input" placeholder={props.placeholder} onChange={()=>setState(event.target.value)} />
    	    <input type="submit" value="clear" onClick={() => setState('')} />
        <div><br/>
            Count: {state.count}<br/>
              <button onClick={() => dispatch({type: 'reset', payload: initialCount})}>
                Reset
              </button><br/>
              <button onClick={() => dispatch({type: 'decrement'})}>-</button><br/>
              <button onClick={() => dispatch({type: 'increment'})}>+</button><br/>
        </div>
        </div>
    );
    
}