import * as modal_fn from '../functions/ModalFunction'
import * as drag_fn from '../functions/DragFunction'
import { GroupData } from '../api/GroupData';
import Accordion from './Accordion'
import Modal from './ModalCore';

import { useState, useEffect, useRef } from 'react'
import { DragDropContext,Droppable,Draggable } from 'react-beautiful-dnd';


function GroupDrag(props) {

  const droppableId =  `${props.type}-${props.id}`

  const [count, setCount] = useState(0)

  const [items, setItems] = useState([]);

  const [isDragged, setIsDragged] = useState(false);

  const [itemsData, setItemsData] = useState(GroupData.list);

  // FOR MODAL
  const [data, setData] = useState(GroupData);
  // FOR MODAL
  const [isModalOpen, setModalOpen] = useState(false);
  // FOR MODAL
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
      return <Accordion id = { item.id } isDragged = { item.isDragged ? true : false } />
    });
    setItems(datas);
  },[itemsData]);

  return (
    <>
    <div className="container mt-4 mb-4 dnd">

      <div className="col-xl-12 col-lg-12 col-md-12 col-sm-12" >

        <h5
          className="display-3"
          r_name = { `Group Title` }
          r_value = { data.title }
          r_type = { `text` }
          r_key = { `title` }
          onClick = { (e) => onClickHandler(e, _fn) }
        >
          { GroupData.title }
        </h5>

        <p
          className="display-6"
          r_name = { `Group Sub Title` }
          r_value = { data.subtitle }
          r_type = { `text` }
          r_key = { `subtitle` }
          onClick = { (e) => onClickHandler(e, _fn) }
        >
          { GroupData.subtitle }
        </p>
      </div>

         <Droppable droppableId = { droppableId } type = { `enable` } >
            {(provided, snapshot) => (
              <div
                {...provided.droppableProps}
                className="accordion"
                ref={provided.innerRef}
                style={getListStyle(snapshot.isDraggingOver, snapshot.draggingOverWith, props, _fn)}
                drag = { snapshot.draggingOverWith }
              >
                {items.map((item, index) => (
                    <Draggable draggableId={ `group-item-${index}`} key={`group-item-${index}`} index={index} >
                      {(provided, snapshot) => (
                       <div
                         {...provided.draggableProps}
                         ref = {provided.innerRef}
                         className="accordion-items dnd m-1"
                        >
                        <div  {...provided.dragHandleProps} className="text-danger" >DRAG THIS</div>
                         { item }
                       </div>
                      )}
                    </Draggable>
                ))}

                { provided.placeholder }
              </div>
            )}
        </Droppable>

      <Modal
       { ...modalAttr }
        onCloseHandler = { onCloseHandler }
        onSaveHandler = { onSaveHandler }
        currentState = { isModalOpen }
        _fn = { _fn }
      />

    </div>

    {props.children}

    </>
  )
}

export default GroupDrag
