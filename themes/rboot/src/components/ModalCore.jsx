import { useState, useEffect, useRef } from 'react'

import * as Core from '@coreui/react';

function ModalCore(props) {

  const [value, setValue] = useState()

  const [subValue, setSubValue] = useState();

  const prevValue = useRef(props.value)

  const imgConverter = (file) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => {
      const base64 = reader.result;
      setValue(base64);
    };
  }

  const onValueChanged = (e) => {

    if (props.rkey === 'img') {
      imgConverter(e.target.files[0])
    }
    else {
      setValue(e.target.value);
    }
    setSubValue(e.target.attributes.r_subval.value);
  }

  const onSubValueChanged = (e) => {
    setSubValue(e.target.value);
  }

  const onKeyDown = (e) => {
     if (e.key === 'Enter') {
      props.onSaveHandler(e, props._fn);
      setValue(null);
      setSubValue(null);
    }
    if (e.key === 'Escape') {
      props.onCloseHandler(e, props._fn);
      setValue(null);
      setSubValue(null);
    }
  }

  return (

    <Core.CModal visible = { props.currentState } >
      <Core.CModalHeader onClose = { (e) => {props.onCloseHandler(e, props._fn);setValue(null)} } >
        <Core.CModalTitle>{ props.name }</Core.CModalTitle>
      </Core.CModalHeader>

      <Core.CModalBody>
        <div>
        {
          props.type == 'link' &&
          <Core.CFormInput
            autoFocus = { true }
            type = { props.type }
            className = "form-control mb-2"
            aria-label = { props.value }
            aria-describedby = "inputGroup-sizing-lg"
            r_data = { value ? value : props.value }
            r_type = { props.type }
            r_key = { props.rkey }
            r_subval = { subValue ? subValue : props.name }
            onChange = { onValueChanged }
            onKeyDown = { onKeyDown }
            value = { value ? value : props.value }
          />
        }
        {
          props.type == 'text' &&
          <Core.CFormInput
            autoFocus = { true }
            type = { props.type }
            className = "form-control mb-2"
            aria-label = { props.value }
            aria-describedby = "inputGroup-sizing-lg"
            r_data = { value ? value : props.value }
            r_type = { props.type }
            r_key = { props.rkey }
            r_subval = { '' }
            onChange = { onValueChanged }
            onKeyDown = { onKeyDown }
            value = { value ? value : props.value }
          />
        }
        {
          props.type == 'file' &&
              <Core.CFormInput
               autoFocus = { true }
               type = { props.type }
               className = "form-control mb-2"
               aria-label = { props.value }
               aria-describedby = "inputGroup-sizing-lg"
               r_data = { value ? value : props.value }
               r_type = { props.type }
               r_key = { props.rkey }
               r_subval = { subValue ? subValue : props.alt }
               onChange = { onValueChanged }
               onKeyDown = { onKeyDown }
             />
        }
        {
          props.rkey == 'img' &&
              <Core.CFormInput
               autoFocus = { true }
               placeholder = { `alt` }
               type = { `text` }
               className = "form-control"
               aria-label = { props.alt }
               aria-describedby = "inputGroup-sizing-lg"
               r_data = { value ? value : props.value }
               r_type = { props.type }
               r_key = { props.rkey }
               r_subval = { subValue ? subValue : props.alt }
               onChange = { onSubValueChanged }
               onKeyDown = { onKeyDown }
               value = { subValue ? subValue : props.alt }
             />
        }
        {
          props.rkey == 'link' &&
              <Core.CFormInput
               autoFocus = { true }
               placeholder = { `Label` }
               type = { `text` }
               className = "form-control"
               aria-label = { props.alt }
               aria-describedby = "inputGroup-sizing-lg"
               r_data = { value ? value : props.value  }
               r_type = { props.type }
               r_key = { props.rkey }
               r_subval = { subValue ? subValue : props.name }
               onChange = { onSubValueChanged }
               onKeyDown = { onKeyDown }
               value = { subValue ? subValue : props.name }
             />
        }

        </div>
        <div className="p-2 pt-4 mt-2 border">
        {
          props.type == 'file' && props.rkey == 'img' &&
          <img src = { value ? value : props.value } className="w-25 mt-2" />
        }
        {
          props.type == 'text' && props.rkey !== 'link' &&
          <p>{ props.value }</p>
        }
        </div>
      </Core.CModalBody>

      <Core.CModalFooter>
        <Core.CButton
          color="secondary"
          onClick = { (e) => props.onCloseHandler(e, props._fn) }
        >
          Close
        </Core.CButton>
        <Core.CButton
          color="primary"
          r_type = { props.type }
          r_key = { props.rkey }
          r_data = { value }
          r_subval = { subValue }
          onClick = { (e) => {props.onSaveHandler(e, props._fn);setValue(null);} }
        >
          Save changes
        </Core.CButton>
      </Core.CModalFooter>

    </Core.CModal>
  )
}

export default ModalCore;

/**/
