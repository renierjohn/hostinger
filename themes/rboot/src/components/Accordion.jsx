import { useState, useEffect, useRef } from 'react'

import { AccordionData } from '../api/AccordionData';
import Modal from './ModalCore';
import * as modal_fn from '../functions/ModalFunction'
import * as drag_fn from '../functions/DragFunction'


function Accordion(props) {

  const [data, setData] = useState([]);

  const [isModalOpen, setModalOpen] = useState(false);

  const [modalAttr, setModalAttr] = useState([]);

  const onClickHandler = modal_fn.default.onClickHandler;

  const onCloseHandler = modal_fn.default.onCloseHandler;

  const onSaveHandler = modal_fn.default.onSaveHandler;

  const _fn = {
    setData: setData,
    setModalOpen: setModalOpen,
    setModalAttr: setModalAttr,
    data: data,
    modalAttr: modalAttr,
    isModalOpen: isModalOpen,
  };

  useEffect( () => {
    const [dataObj] = AccordionData.list.filter( (item, index) => {
      return item.id === props.id
    });

    setData(dataObj)
    return(() => {
      // console.log('unmount', props.id)
    })
  },[props.id, data]);

  return (
    <>
      <h2 className="accordion-header">
        <button
           className="accordion-button"
           type="button"
           data-bs-toggle= { data ? `collapse` : ``}
           data-bs-target={ data.id }
           aria-expanded= { data ? true : false}
           aria-controls="collapse-${ data.id}"
           r_name = { `Accordion Title` }
           r_value = { data.title }
           r_key = { `title` }
           r_type = { `text` }
           onClick = { (e) => onClickHandler(e, _fn) }
         >
          { data && data.title }
        </button>
      </h2>

      <div
        className="accordion-collapse collapse show "
        aria-labelledby = { data.id}
        data-bs-parent="#accordionExample"
      >
        <div
          className="accordion-body"
          r_name = { `Accordion Body` }
          r_value = { data.body }
          r_key = { `body` }
          r_type = { `text` }
          onClick = { (e) => onClickHandler(e, _fn) }
        >
         { data.body }
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
