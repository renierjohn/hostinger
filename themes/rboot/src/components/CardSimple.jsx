import { useState, useEffect, useRef } from 'react'
import { DragDropContext,Droppable,Draggable } from 'react-beautiful-dnd';

import { CardSimpleData } from '../api/CardSimpleData';
import Modal from './ModalCore';
import * as modal_fn from '../functions/ModalFunction'
import * as drag_fn from '../functions/DragFunction'



function CardSimple(props) {

  const [data, setData] = useState([]);

  const [isModalOpen, setModalOpen] = useState(false);

  const [modalAttr, setModalAttr] = useState();

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
    const [dataObj] = CardSimpleData.list.filter( (item, index) => {
      return item.id === props.id
    });

    setData(dataObj)

    return(() => {
      // console.log('unmount', props.id)
    })
  },[props.id, data]);

  return (
    <>
      <div className="card" r_id = { data.id } >
        <img src = { data.img && data.img.url }
          alt = { data.img && data.img.alt }
          className = "card-img-top component-img"
          r_name = { `Card Image` }
          r_value = { data.img && data.img.url }
          r_subval = { data.img && data.img.alt }
          r_type = { `file` }
          r_key = { `img` }
          onClick = { (e) => onClickHandler(e, _fn) }
        />
            <div className="card-body d-flex flex-column">
              <h5
                className="card-title"
                r_name = { `Card Title` }
                r_type = { `text` }
                r_key = { `title` }
                r_value = { data.title }
                onClick = { (e) => onClickHandler(e, _fn) }
              >
                { data.title }
              </h5>
              <span
                className="text-end"
                r_name = { `Card Category` }
                r_type = { `taxonomy` }
                r_key = { `taxonomy` }
                r_value = { `Beach` }
                onClick = { (e) => onClickHandler(e, _fn) }
              >
                Beach
              </span>


          <p
            className="card-text mb-4"
            r_name = { `Card Body` }
            r_type = { `text` }
            r_key = { `body` }
            r_value = { data.body }
            onClick = { (e) => onClickHandler(e, _fn) }
          >
            { data.body }
          </p>
          <a
            href="#"
            className="btn btn-primary mt-auto align-self-start"
            r_name = { data.link && data.link.name }
            r_type = { `link` }
            r_key = { `link` }
            r_value = { data.link && data.link.url }
            r_subval = { data.link && data.link.name }
            onClick = { (e) => onClickHandler(e, _fn) }
          >
            { data.link && data.link.name }
          </a>
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

export default CardSimple

/**/
