import * as modal_fn from '../functions/ModalFunction'
import * as drag_fn from '../functions/DragFunction'
import CardSimple from './CardSimple'
import CardSimpleBase from './CardSimpleBase'
import CardAdvance from './CardAdvance'
import Modal from './ModalCore';
import { CardGroupData } from '../api/CardGroupData';

import { useState, useEffect, useRef } from 'react'
import { DragDropContext,Droppable,Draggable } from 'react-beautiful-dnd';


function CardGroupDrag(props) {

  const droppableId =  `${props.type}-${props.id}`

  const [count, setCount] = useState(0)

  const [data, setData] = useState(CardGroupData);

  const [items, setItems] = useState([]);

  const [itemsData, setItemsData] = useState(CardGroupData.list);

  const [isDragged, setIsDragged] = useState(false);

  const [isModalOpen, setModalOpen] = useState(false);

  const [modalAttr, setModalAttr] = useState([]);

  const onClickHandler = modal_fn.default.onClickHandler;

  const onCloseHandler = modal_fn.default.onCloseHandler;

  const onSaveHandler = modal_fn.default.onSaveHandler;

  const onDragEnd = drag_fn.default.onDragEnd;

  const onDragStart = drag_fn.default.onDragStart;

  const onDragUpdate = drag_fn.default.onDragUpdate;

  const getListStyle = drag_fn.default.getListStyle;

  const _fn = {
    setItems:setItems,
    setItemsData: setItemsData,
    setData: setData,
    setModalOpen: setModalOpen,
    setModalAttr: setModalAttr,
    data: data,
    modalAttr: modalAttr,
    isModalOpen: isModalOpen,
    itemsData: itemsData,
    droppableId: droppableId
  };

  useEffect(() => {
    const datas = itemsData.map((item, index) => {
      return <CardSimple id = { item.id } isDragged = { item.isDragged ? true : false } />
    });
    setItems(datas)
  },[itemsData]);

  return (
    <>
      <div className="bg-light mt-4 mb-4 shadow-sm dnd">
        <div className="container">
          <div className="row pt-5">
            <div className="col-12 text-center">
              <h3
                className="text-uppercase border-bottom mb-4"
                r_name = { `Card Group Title` }
                r_value = { data.title }
                r_type = { `text` }
                r_key = { `title` }
                onClick = { (e) => onClickHandler(e, _fn) }
              >{ data.title }</h3>
              <p
                r_name = { `Card Group Sub-Title` }
                r_value = { data.subtitle }
                r_type = { `text` }
                r_key = { `subtitle` }
                onClick = { (e) => onClickHandler(e, _fn) }
              >
              { data.subtitle }</p>
            </div>
          </div>

          <Droppable droppableId = { droppableId } direction="horizontal" type = { `enable` } >
            {(provided, snapshot) => (
                <div
                  {...provided.droppableProps}
                  className="row"
                  ref={provided.innerRef}
                  style={getListStyle(snapshot.isDraggingOver)} >

                  {items.map((item, index) => (
                      <Draggable draggableId={ `card-group-item-${index}`} key={`card-group-item-${index}`} index={index} >
                          {(provided, snapshot) => (
                             <div
                               {...provided.draggableProps}
                               {...provided.dragHandleProps}
                               ref = {provided.innerRef}
                               className="col-lg-4 col-md-4 col-sm-2 mb-3 d-flex align-items-stretch dnd"
                              >
                              { item }
                             </div>
                          )}
                      </Draggable>
                  ))}
                { provided.placeholder }
                </div>
            )}
          </Droppable>


        </div>

      <Modal
       { ...modalAttr }
        onCloseHandler = { onCloseHandler }
        onSaveHandler = { onSaveHandler }
        currentState = { isModalOpen }
        _fn = { _fn }
      />

      </div>
      { props.children }
      </>
  )
}

export default CardGroupDrag
