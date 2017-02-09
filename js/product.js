function del(obj){
  var table = obj.parentNode.parentNode.parentNode;
  table.deleteRow(obj.parentNode.parentNode.rowIndex); 
}

