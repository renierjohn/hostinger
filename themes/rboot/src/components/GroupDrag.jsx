// import { GroupData } from '../api/GroupData';
import RestData from '../api/RestData';

import * as modal_fn from '../functions/ModalFunction'
import * as drag_fn from '../functions/DragFunction'
import Accordion from './Accordion'
import Modal from './ModalCore';

import { useState, useEffect, useRef } from 'react'
import { DragDropContext,Droppable,Draggable } from 'react-beautiful-dnd';


function GroupDrag(props) {

  const droppableId =  `${props.type}-${props.id}`

  const [count, setCount] = useState(0)

  const [items, setItems] = useState([]);

  const [isDragged, setIsDragged] = useState(false);

  const [itemsData, setItemsData] = useState([]);

  // FOR MODAL
  // const [data, setData] = useState(GroupData);

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

  const [data, setData] = useState({});

  const {restData, restLoading} = RestData({ key: props.machine_name, id: props.id });

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
    if (!restLoading) {
      const datas = restData['field_group_list'].map((item, index) => {
        return <Accordion id = { item['target_uuid'] } type = { `component` } isDragged = { item.isDragged ? true : false } />
      });
      setItems(datas);
    }

  },[ props.id, restLoading]);

  return (
    <>
    <div className="container mt-4 mb-4 dnd">
      <div className="col-xl-12 col-lg-12 col-md-12 col-sm-12" >
        <h5
          className="display-3"
          r_name = { `Group Title` }
          r_value = { restLoading ? `Loading...` : restData['field_group_title'][0] ?  restData['field_group_title'][0]['value'] : '' }
          r_type = { `text` }
          r_key = { `title` }
          onClick = { (e) => onClickHandler(e, _fn) }
        >
          { restLoading ? `Loading...` : restData['field_group_title'][0] ?  restData['field_group_title'][0]['value'] : 'N/A' }
        </h5>

        <p
          className="display-6"
          r_name = { `Group Sub Title` }
          r_value = { restLoading ? `Loading...` : restData['field_group_sub_title'][0] ?  restData['field_group_sub_title'][0]['value'] : '' }
          r_type = { `text` }
          r_key = { `subtitle` }
          onClick = { (e) => onClickHandler(e, _fn) }
        >
          { restLoading ? `Loading...` : restData['field_group_sub_title'][0] ?  restData['field_group_sub_title'][0]['value'] : 'N/A' }
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
