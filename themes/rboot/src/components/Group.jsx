
import { GroupData } from '../api/GroupData';
import Accordion from './Accordion'
// import Modal from './Modal';
import Modal from './ModalCore';
import { useState, useEffect, useRef } from 'react'
import { DragDropContext,Droppable,Draggable } from 'react-beautiful-dnd';


function usePrevious(value) {
  const ref = useRef();
  useEffect(() => {
    ref.current = value;
  },[value]);
  return ref.current;
}

function Group(props) {

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

  const getListStyle = (isDraggingOver) => ({
    background: isDraggingOver ? "lightblue" : "lightgrey",
  });

  // FOR MODAL
  const onClickHandler = (e) => {
    e.preventDefault()
    if (e.target.attributes.r_name) {
      const name = e.target.attributes.r_name.value;
      const type = e.target.attributes.r_type.value;
      const key = e.target.attributes.r_key.value;
      const value = e.target.attributes.r_value.value;
      const attrs = {
        rkey: key,
        type: type,
        name: name,
        value: value
      }
      setModalAttr(attrs)
    }
    setModalOpen(isModalOpen ? false : true)
  };
  // FOR MODAL
  const onCloseHandler = (e) => {
    e.preventDefault()
    setModalOpen(isModalOpen ? false : true)
  }
  // FOR MODAL
  const onSaveHandler = (e) => {
    e.preventDefault()
    const modalValue = e.target.attributes.r_data.value;
    const modalType = e.target.attributes.r_type.value;
    const modalKey = e.target.attributes.r_key.value;
    updateState(modalKey, modalValue);
    setModalOpen(isModalOpen ? false : true)
  }
  // FOR MODAL
  const updateState = (key, value) => {
    setData((prevData) => {
      prevData[key] = value
      return prevData
    });
  }

  const onDragEnd = (data) => {
    if (!data.destination) return;
    const startIndex = data.source.index;
    const endIndex = data.destination.index;
    const result = [...itemsData];
    const [removed] = result.splice(startIndex, 1);
    result.splice(endIndex, 0, removed);

    setItemsData((prevItemsData) => {
      const result = [...prevItemsData];
      const resultItemData = result.map((item, index) => {
        if (index === startIndex || index === endIndex) {
          item['isDragged'] = true;
        } else {
          item['isDragged'] = false;
        }
        return item
      });
      const [removed] = resultItemData.splice(startIndex, 1);
      resultItemData.splice(endIndex, 0, removed);
      return resultItemData;
    });

  }

  useEffect(() => {
    const datas = itemsData.map((item, index) => {
      return <Accordion id = { item.id } isDragged = { item.isDragged ? true : false } />
    });
    setItems(datas)
  },[itemsData]);

  return (
    <div className="container mt-4 mb-4 dnd">

      <div className="col-xl-12 col-lg-12 col-md-12 col-sm-12" >
        <h5
          className="display-3"
          r_name = { `Group Title` }
          r_value = { data.title }
          r_type = { `text` }
          r_key = { `title` }
          onClick = { onClickHandler }
        >{ GroupData.title }</h5>
        <p
          className="display-6"
          r_name = { `Group Sub Title` }
          r_value = { data.subtitle }
          r_type = { `text` }
          r_key = { `subtitle` }
          onClick = { onClickHandler }
        >{ GroupData.subtitle }</p>
      </div>

          <DragDropContext onDragEnd={onDragEnd}>
             <Droppable droppableId="drop" type="DEFAULT">
                {(provided, snapshot) => (
                  <div
                    {...provided.droppableProps}
                    className="accordion"
                    ref={provided.innerRef}
                    style={getListStyle(snapshot.isDraggingOver)} >
                    {items.map((item, index) => (
                        <Draggable draggableId={ `item-${index}`} key={`item-${index}`} index={index} >
                          {(provided, snapshot) => (
                           <div
                             {...provided.draggableProps}

                             ref = {provided.innerRef}
                             className="accordion-items dnd"
                            >
                            <div  {...provided.dragHandleProps} >Drag Here</div>
                             { item }
                           </div>
                          )}
                        </Draggable>
                    ))}

                    { provided.placeholder }
                  </div>
                )}
            </Droppable>
          </DragDropContext>

      <Modal
       { ...modalAttr }
        onCloseHandler = { onCloseHandler }
        onSaveHandler = { onSaveHandler }
        currentState = { isModalOpen }
      />

    </div>
  )
}

export default Group
