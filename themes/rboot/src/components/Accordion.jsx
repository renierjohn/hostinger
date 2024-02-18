// import { AccordionData } from '../api/AccordionData';
import RestData from '../api/RestData';

import Modal from './ModalCore';
import * as modal_fn from '../functions/ModalFunction'
import * as drag_fn from '../functions/DragFunction'

import { useState, useEffect, useRef } from 'react'

function Accordion(props) {

  const [data, setData] = useState([]);

  const [isModalOpen, setModalOpen] = useState(false);

  const [modalAttr, setModalAttr] = useState([]);

  const onClickHandler = modal_fn.default.onClickHandler;

  const onCloseHandler = modal_fn.default.onCloseHandler;

  const onSaveHandler = modal_fn.default.onSaveHandler;

  const { restData, restLoading } = RestData({ key: props.type, id: props.id });

  const _fn = {
    setData: setData,
    setModalOpen: setModalOpen,
    setModalAttr: setModalAttr,
    data: data,
    modalAttr: modalAttr,
    isModalOpen: isModalOpen,
  };

  useEffect( () => {
    if (!restLoading) {
      setData(restData)
    }
    return(() => {})
  },[restLoading, props.id, data]);

  return (
    <>
      <h2 className="accordion-header">
        <button
           className="accordion-button"
           type="button"
           data-bs-toggle= { data ? `collapse` : ``}
           data-bs-target={ data['id'] && data['id'][0]['value'] }
           aria-expanded= { data ? true : false}
           aria-controls="collapse-${ data.id}"
           r_name = { `Accordion Title` }
           r_value = { restLoading ? 'loading' : restData['field_accordion_title'][0] ? restData['field_accordion_title'][0]['value'] : '' }
           r_key = { `title` }
           r_type = { `text` }
           onClick = { (e) => onClickHandler(e, _fn) }
         >
          { restLoading ? 'loading' :  restData['field_accordion_title'][0] ? restData['field_accordion_title'][0]['value'] : 'N/A' }
        </button>
      </h2>

      <div
        className="accordion-collapse collapse show "
        aria-labelledby = { restData['id'] && restData['id'][0]['value'] }
        data-bs-parent="#accordionExample"
      >
        <div
          className="accordion-body"
          r_name = { `Accordion Body` }
          r_value = { restLoading ? 'loading' :  restData['field_accordion_body'] ? restData['field_accordion_body'][0]['value'] : '' }
          r_key = { `body` }
          r_type = { `text` }
          onClick = { (e) => onClickHandler(e, _fn) }
        >
         { restLoading ? 'Loading...' :  restData['field_accordion_body'] ? restData['field_accordion_body'][0]['value'] : 'N/A'}
        </div>
      </div>

      {/* POP UP*/}
      <Modal
       { ...modalAttr }
        onCloseHandler = { onCloseHandler }
        onSaveHandler = { onSaveHandler }
        currentState = { isModalOpen }
        _fn = { _fn }
      />
    </>
  )
}

export default Accordion
