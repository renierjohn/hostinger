
import CardSimple from './CardSimple'
import CardSimpleBase from './CardSimpleBase'
import CardAdvance from './CardAdvance'
import Modal from './Modal';
import Wrapper from './Wrapper'

import { CardGroupData } from '../api/CardGroupData';

import { useState, useEffect, useRef } from 'react'
import { DragDropContext,Droppable,Draggable } from 'react-beautiful-dnd';

function usePrevious(value) {
  const ref = useRef();
  useEffect(() => {
    ref.current = value;
  },[value]);
  return ref.current;
}

function CardGroup() {

  const [count, setCount] = useState(0)

  const [data, setData] = useState(CardGroupData);

  const [items, setItems] = useState([]);

  const [itemsData, setItemsData] = useState(CardGroupData.list);

  const [isDragged, setIsDragged] = useState(false);

  const [isModalOpen, setModalOpen] = useState(false);

  const [modalAttr, setModalAttr] = useState([]);

  const getListStyle = (isDraggingOver) => ({
    background: isDraggingOver ? "lightblue" : "lightgrey",
  });

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

  const onCloseHandler = (e) => {
    e.preventDefault()
    setModalOpen(isModalOpen ? false : true)
  }

  const onSaveHandler = (e) => {
    e.preventDefault()
    const modalValue = e.target.attributes.r_data.value;
    const modalType = e.target.attributes.r_type.value;
    const modalKey = e.target.attributes.r_key.value;
    updateState(modalKey, modalValue);
    setModalOpen(isModalOpen ? false : true)
  }

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
      return <CardSimple id = { item.id } isDragged = { item.isDragged ? true : false } />
    });
    setItems(datas)
    // return (() => {return datas})
  },[itemsData]);

  const attribs = ['bg-light pt-5 pb-5 shadow-sm dnd'];

  return (
      <>
      <Wrapper attribs={attribs} >
      // <div className="bg-light pt-5 pb-5 shadow-sm dnd">
        <DragDropContext onDragEnd={onDragEnd}>
        <div className="container">
          <div className="row pt-5">
            <div className="col-12 text-center">
              <h3
                className="text-uppercase border-bottom mb-4"
                r_name = { `Card Group Title` }
                r_value = { data.title }
                r_type = { `text` }
                r_key = { `title` }
                onClick = { onClickHandler }
              >{ data.title }</h3>
              <p
                r_name = { `Card Group Sub-Title` }
                r_value = { data.subtitle }
                r_type = { `text` }
                r_key = { `subtitle` }
                onClick = { onClickHandler }
              >
              { data.subtitle }</p>
            </div>
          </div>

             <Droppable droppableId="drop" direction="horizontal" type="component">
                {(provided, snapshot) => (
                    <div
                      {...provided.droppableProps}
                      className="row"
                      ref={provided.innerRef}
                      style={getListStyle(snapshot.isDraggingOver)} >

                      {items.map((item, index) => (
                          <Draggable draggableId={ `item-${index}`} key={`item-${index}`} index={index} >
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
      />

          </DragDropContext>
      // </div>
          </Wrapper>
      </>
  )
}

export default CardGroup
