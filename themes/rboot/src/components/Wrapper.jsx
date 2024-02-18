import { useState, useEffect, useRef } from 'react'

import Element from './Element'

function Wrapper(props) {
  // const wrapper_data = ['second-level', 'first-level'];
  // const array_classes = wrapper_data.map((dat) => {
  //   return (children) => <div className={dat} >{children}</div>
  // })

  const ApplyWrappers = ({ wrappers, children }) => wrappers.reduce(
    (children, wrapper) => wrapper ? wrapper(children) : children,
    children
  );

  const array_classes = props.attribs.map((dat) => {
    return (children) => <div className={dat} >{children}</div>
  })


console.log(ApplyWrappers)
  return (
    <>
      {/*<Element type='div' attrib='class' attrib_val='test'>Sample</Element>*/}
      <ApplyWrappers wrappers={array_classes}>
       {props.children}
     </ApplyWrappers>
    </>
  )
}

export default Wrapper
