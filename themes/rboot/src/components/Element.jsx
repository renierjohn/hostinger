import { useState, useEffect, useRef } from 'react'

function Element(props) {

  // const [element, setElement] = useState(null)

  // const element = props.type.map((dat) => {
  //   return <div>Sample {dat}</div>
  // })

  // setElement()
  var element;
  switch(props.type) {
    case 'div':
      element = <div >{props.children}</div>
    break;
  }


  return (
    <>
      { element }
    </>
  )
}

export default Element
