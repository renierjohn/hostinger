// import { CardGroupData } from '../api/CardGroupData';
import  RestData  from '../api/RestData';

import * as modal_fn from '../functions/ModalFunction'
import * as drag_fn from '../functions/DragFunction'
import CardSimple from './CardSimple'
import CardSimpleBase from './CardSimpleBase'
import CardAdvance from './CardAdvance'
import Modal from './ModalCore';

import Wrapper from './Wrapper'

import { useState, useEffect, useRef } from 'react'
import { DragDropContext,Droppable,Draggable } from 'react-beautiful-dnd';


function CardGroupDrag(props) {

  const droppableId =  `${props.type}-${props.id}`

  const [count, setCount] = useState(0)

  const [data, setData] = useState([]);

  const [items, setItems] = useState([]);

  const [itemsData, setItemsData] = useState([]);

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

  const { restData, restLoading } = RestData({ key: props.machine_name, id: props.id });

  const _fn = {
    setItems:setItems,
    setItemsData: setItemsData,
    setData: setData,
    setModalOpen: setModalOpen,
    setModalAttr: setModalAttr,
    modalAttr: modalAttr,
    isModalOpen: isModalOpen,
    itemsData: itemsData,
    droppableId: droppableId
  };

  useEffect(() => {
   console.log(restLoading, restData);
    if (!restLoading) {
      const datas = restData['field_card_type'].map((item, index) => {
        return <CardSimple id = { item.target_uuid } type = { `component` } isDragged = { item.isDragged ? true : false } />
      });
      setItems(datas);
    }

  },[props.id, restLoading]);


  const attribs = ['bg-light pt-5 pb-5 shadow-sm dnd', 'custom-class-wrap'];

  return (
    <>
     {/* <div className="bg-light mt-4 mb-4 shadow-sm dnd">*/}
    <Wrapper attribs={attribs}>
        <div className="container">
          <div className="row pt-5">
            <div className="col-12 text-center">
                <h3
                  className="text-uppercase border-bottom mb-4"
                  r_name = { `Card Group Title` }
                  r_value = { restLoading ? `Loading...` : restData['field_card_group_title'][0] ? restData['field_card_group_title'][0]['value'] : 'N/A' }
                  r_type = { `text` }
                  r_key = { `title` }
                  onClick = { (e) => onClickHandler(e, _fn) }
                >{ restLoading ? `Loading...` : restData['field_card_group_title'][0] ? restData['field_card_group_title'][0]['value'] : 'N/A' }
                </h3>
                <p
                  r_name = { `Card Group Sub-Title` }
                  r_value = { restLoading ? `Loading...` : restData['field_card_group_sub_title'][0] ? restData['field_card_group_sub_title'][0]['value'] : 'N/A' }
                  r_type = { `text` }
                  r_key = { `subtitle` }
                  onClick = { (e) => onClickHandler(e, _fn) }
                >
                { restLoading ? `Loading...` : restData['field_card_group_sub_title'][0] ? restData['field_card_group_sub_title'][0]['value'] : 'N/A' }
                </p>
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
        { props.children }
      </Wrapper>
      </>
  )
}

export default CardGroupDrag
