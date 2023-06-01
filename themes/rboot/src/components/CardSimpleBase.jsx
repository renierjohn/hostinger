import { useState, useEffect } from 'react'
import { DragDropContext,Droppable,Draggable } from 'react-beautiful-dnd';
import Modal from './Modal';

function CardSimple(props) {

  const [dataProps, setData] = useState(props);

  const [items, setItems] = useState([]);

  const [isModalOpen, setModalOpen] = useState(false);

  const [componentName, setComponentName] = useState('');

  const [componentType, setComponentType] = useState('');

  const [componentKey, setComponentKey] = useState('');

  const [componentValue, setComponentValue] = useState('');

  const getListStyle = (isDraggingOver, itemsLength) => ({
    background: isDraggingOver ? "lightgreen" : "lightgrey",
  });

  const onDragEnd = (data) => {
    if (!data.destination) return;
    const startIndex = data.source.index;
    const endIndex = data.destination.index;
    const result = Array.from(items);
    const [removed] = result.splice(startIndex, 1);
    result.splice(endIndex, 0, removed);
    setItems(result)
  }

  const onClickHandler = (e) => {
    e.preventDefault()
    if (e.target.attributes.r_name) {
      const name = e.target.attributes.r_name.value;
      const type = e.target.attributes.r_type.value;
      const key = e.target.attributes.r_key.value;
      const value = e.target.attributes.r_value.value;
      setComponentName(name);
      setComponentType(type);
      setComponentKey(key)
      setComponentValue(value);
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
    // const currentState = {...props}
    // currentState[key] = value;
    // setData(currentState);
 }

  const lists = [
    {id:1, src:'NAME A', dest:'Name dest A'},
    {id:2, src:'NAME B', dest:'Name dest B'},
    {id:3, src:'NAME C', dest:'Name dest C'},
  ];

  useEffect( () => {
    setItems(lists)
    // setData(props)
  },[]);

  return (
    <>
      <div className="card">
        <img src = { dataProps.img.url }
          alt = { dataProps.img.alt }
          className = "card-img-top"
          r_name = { `Card Image` }
          r_value = { dataProps.img.url }
          r_type = { `image` }
          r_key = { `img` }
          onClick = { onClickHandler }
        />
            <div className="card-body d-flex flex-column">
              <h5
                className="card-title"
                r_name = { `Card Title` }
                r_type = { `text` }
                r_key = { `title` }
                r_value = { dataProps.title }
                onClick = { onClickHandler }
              >
                { dataProps.title }
              </h5>
              <span
                className="text-end"
                r_name = { `Card Category` }
                r_type = { `taxonomy` }
                r_key = { `taxonomy` }
                r_value = { `Beach` }
                onClick = { onClickHandler }
              >
                Beach
              </span>

           <DragDropContext onDragEnd = { onDragEnd}>
                 <Droppable droppableId="item-0">
                    {(provided, snapshot) => (
                        <ol className="list-group list-group" {...provided.droppableProps} ref = { provided.innerRef}  style = { getListStyle(snapshot.isDraggingOver, 10)} >
                            {items.map((item, index) => (
                                <Draggable draggableId = { `item-${item.id}`} key = { `item-${item.id}`} index = { index} >
                                    {(provided, snapshot) => (
                                      <li
                                         ref = { provided.innerRef}
                                         {...provided.draggableProps}
                                         {...provided.dragHandleProps}
                                         className="list-group-item d-flex justify-content-between align-items-start"
                                       >
                                       <div className="ms-2 me-auto">
                                         <div className="fw-bold">{ item.src }</div>
                                           { item.dest }
                                         </div>
                                      </li>
                                    )}
                                </Draggable>
                            ))}
                        </ol>
                    )}
                </Droppable>
              </DragDropContext>

          <p
            className="card-text mb-4"
            r_name = { `Card Body` }
            r_type = { `text` }
            r_key = { `body` }
            r_value = { dataProps.body }
            onClick = { onClickHandler }
          >
            { dataProps.body }
          </p>
          <a
            href="#"
            className="btn btn-primary mt-auto align-self-start"
            r_name = { dataProps.link.name }
            r_type = { dataProps.link.type }
            r_key = { `link` }
            r_value = { dataProps.link.url }
            onClick = { onClickHandler }
          >
            { dataProps.link.name }
          </a>
        </div>
      </div>
      {/* POP UP*/}
      <Modal
        onCloseHandler = { onCloseHandler }
        onSaveHandler = { onSaveHandler }
        currentState = { isModalOpen }
        name = { componentName }
        type = { componentType }
        r_key = { componentKey }
        value = { componentValue }
      />
    </>
  )
}

export default CardSimple

/**/
