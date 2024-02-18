// import { CardSimpleData } from '../api/CardSimpleData';
import  RestData  from '../api/RestData';

import Modal from './ModalCore';
import * as modal_fn from '../functions/ModalFunction'
import * as drag_fn from '../functions/DragFunction'

import { useState, useEffect, useRef } from 'react'
import { DragDropContext,Droppable,Draggable } from 'react-beautiful-dnd';


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

  const { restData, restLoading } = RestData({key: props.type, id: props.id});

  useEffect( () => {
    if (!restLoading) {
      setData(restData)
    }
    return(() => {})
  },[restLoading, props.id, data]);

  return (
    <>
      <div className="card" r_id = { data.id } >
        <img src = { data['field_card_image'] && data['field_card_image']['0']['url'] }
          alt = { data['field_card_image'] && data['field_card_image']['0']['alt'] }
          className = "card-img-top component-img"
          r_name = { `Card Image` }
          r_value = { data['field_card_image'] && data['field_card_image']['0']['url'] }
          r_subval = { data['field_card_image'] && data['field_card_image']['0']['alt'] }
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
                r_value = { data['field_card_title'] && data['field_card_title'][0]['value'] }
                onClick = { (e) => onClickHandler(e, _fn) }
              >
                { data['field_card_title'] && data['field_card_title'][0]['value'] }
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
            r_value = { data['field_card_body'] && data['field_card_body'][0]['value'] }
            onClick = { (e) => onClickHandler(e, _fn) }
          >
            { data['field_card_body'] && data['field_card_body'][0]['value'] }
          </p>
          <a
            href="#"
            className= { data['field_card_cta'] && data['field_card_cta'][0]['options']['attributes']['class'] }
            r_name = { data['field_card_cta'] && data['field_card_cta'][0]['title'] }
            r_type = { `link` }
            r_key = { `link` }
            r_value = { data['field_card_cta'] && data['field_card_cta'][0]['uri'] }
            r_subval = { data['field_card_cta'] && data['field_card_cta'][0]['title'] }
            onClick = { (e) => onClickHandler(e, _fn) }
          >
            { data['field_card_cta'] && data['field_card_cta'][0]['title'] }
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
